<?php

require_once __DIR__.'/../../../system/library/vendor/autoload.php';

class ControllerPaymentPayabbhi extends Controller
{
    public function index()
    {
        $data['button_confirm'] = $this->language->get('button_confirm');

        $this->load->model('checkout/order');

        $oc_order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $payabbhi_order_id = null;
        if (!empty($this->session->data['payabbhi_order_id'])) {
            $payabbhi_order_id = $this->session->data['payabbhi_order_id'];
        }
        try
        {
          if (($payabbhi_order_id === null) or
              (($payabbhi_order_id and ($this->verify_order_amount($payabbhi_order_id, $oc_order)) === false)))
          {
              $payabbhi_order_id = $this->create_payabbhi_order($this->session->data['order_id']);
          }
        } catch (\Payabbhi\Error $e) {
            echo 'Payabbhi Error: ' . $e->getMessage();
            return;
        } catch (Exception $e) {
            echo 'OpenCart Error: ' . $e->getMessage();
            return;
        }

        $data['access_id'] = $this->config->get('payabbhi_access_id');
        $data['currency_code'] = $oc_order['currency_code'];
        $data['total'] = $this->currency->format($oc_order['total'], $oc_order['currency_code'], $oc_order['currency_value'], false) * 100;
        $data['merchant_order_id'] = $this->session->data['order_id'];
        $data['card_holder_name'] = $oc_order['payment_firstname'].' '.$oc_order['payment_lastname'];
        $data['email'] = $oc_order['email'];
        $data['phone'] = $oc_order['telephone'];
        $data['name'] = $this->config->get('config_name');
        $data['lang'] = $this->session->data['language'];
        $data['return_url'] = $this->url->link('payment/payabbhi/callback', '', 'true');
        $data['payabbhi_order_id'] = $payabbhi_order_id;

        if (file_exists(DIR_TEMPLATE.$this->config->get('config_template').'/template/payment/payabbhi.tpl'))
        {
            return $this->load->view($this->config->get('config_template').'/template/payment/payabbhi.tpl', $data);
        }
        else
        {
            return $this->load->view('payment/payabbhi.tpl', $data);
        }
    }

    protected function create_payabbhi_order($merchant_order_id)
    {
      $client = new \Payabbhi\Client($this->config->get('payabbhi_access_id'), $this->config->get('payabbhi_secret_key'));
      $oc_order = $this->model_checkout_order->getOrder($merchant_order_id);
      $payabbhi_order_params = array('merchant_order_id'    => $merchant_order_id,
                            'amount'               => $this->currency->format($oc_order['total'], $oc_order['currency_code'], $oc_order['currency_value'], false) * 100,
                            'currency'             => $oc_order['currency_code'],
                            'payment_auto_capture' => ($this->config->get('payabbhi_payment_auto_capture') === 'true')
                      );
      $payabbhi_order_id = $client->order->create($payabbhi_order_params)->id;
      $this->session->data['payabbhi_order_id'] = $payabbhi_order_id;

      return $payabbhi_order_id;
    }

    protected function verify_order_amount($payabbhi_order_id, $oc_order)
    {
        $client = new \Payabbhi\Client($this->config->get('payabbhi_access_id'), $this->config->get('payabbhi_secret_key'));

        try {
          $payabbhi_order = $client->order->retrieve($payabbhi_order_id);
        } catch(Exception $e) {
            return false;
        }

        $payabbhi_order_args = array(
            'id'                  => $payabbhi_order_id,
            'amount'              => (int)($this->currency->format($oc_order['total'], $oc_order['currency_code'], $oc_order['currency_value'], false) * 100),
            'currency'            => $oc_order['currency_code'],
            'merchant_order_id'   => (string) $this->session->data['order_id'],
        );

        $orderKeys = array_keys($payabbhi_order_args);

        foreach ($orderKeys as $key)
        {
            if ($payabbhi_order_args[$key] !== $payabbhi_order[$key])
            {
                return false;
            }
        }

        return true;
    }


    public function callback()
    {
        $this->load->model('checkout/order');

        if ($this->request->request['payment_id'])
        {
            $payabbhi_payment_id = $this->request->request['payment_id'];
            $merchant_order_id = $this->session->data['order_id'];
            $payabbhi_order_id = $this->session->data['payabbhi_order_id'];
            $payment_signature = $this->request->request['payment_signature'];

            $oc_order = $this->model_checkout_order->getOrder($merchant_order_id);

            $client = new \Payabbhi\Client($this->config->get('payabbhi_access_id'), $this->config->get('payabbhi_secret_key'));

            $attributes = array(
              'payment_id'        => $payabbhi_payment_id,
              'order_id'          => $payabbhi_order_id,
              'payment_signature' => $payment_signature
            );

            $success = false;
            $error = "";

            try
            {
                $client->utility->verifyPaymentSignature($attributes);
                $success = true;
            }
            catch (\Payabbhi\Error $e)
            {
                $error .= $e->getMessage();
            }

            if ($success === true)
            {
                $this->model_checkout_order->addOrderHistory($merchant_order_id, $this->config->get('payabbhi_order_status_id'), 'Payment Successful. Payabbhi Payment ID:'.$payabbhi_payment_id . 'Payabbhi Order ID: ' . $payabbhi_order_id );

                echo '<html>'."\n";
                echo '<head>'."\n";
                echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/success').'">'."\n";
                echo '</head>'."\n";
                echo '<body>'."\n";
                echo '  <p>Please follow <a href="'.$this->url->link('checkout/success').'">link</a>!</p>'."\n";
                echo '</body>'."\n";
                echo '</html>'."\n";
                exit();
            }
            else
            {
                $this->model_checkout_order->addOrderHistory($merchant_order_id, 10, $error.' Payment Failed! Check Payabbhi Portal for details of Payment ID: '.$payabbhi_payment_id . 'Payabbhi Order ID: ' . $payabbhi_order_id);
                echo '<html>'."\n";
                echo '<head>'."\n";
                echo '  <meta http-equiv="Refresh" content="0; url='.$this->url->link('checkout/failure').'">'."\n";
                echo '</head>'."\n";
                echo '<body>'."\n";
                echo '  <p>Please follow <a href="'.$this->url->link('checkout/failure').'">link</a>!</p>'."\n";
                echo '</body>'."\n";
                echo '</html>'."\n";
                exit();
            }
        }
        else
        {
            if (isset($_POST['error']) === true)
            {
                $error = $_POST['error'];

                $message = 'An error occured. Message : ' . $error['message'] . '. Type : ' . $error['type'];

                if (isset($error['field']) === true)
                {
                    $message .= 'Field : ' . $error['field'];
                }
            }
            else
            {
                $message = 'An error occured. Please contact administrator for assistance';
            }

            echo $message;
        }
    }

}
