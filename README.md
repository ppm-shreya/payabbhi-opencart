# Payabbhi Payments - OpenCart Integration

This extension builds on top of Payabbhi PHP Library to provide seamless integration of [Payabbhi Checkout](https://payabbhi.com/docs/checkout) with OpenCart 2.2.x.


### Installation

Make sure you have signed up for your [Payabbhi Account](https://payabbhi.com/docs/account) and downloaded the [API keys](https://payabbhi.com/docs/account/#api-keys) from the [Portal](https://payabbhi.com/portal).

1. Unzip [payabbhi-opencart-VERSION.zip](https://github.com/payabbhi/payabbhi-opencart/releases) and copy the contents of `admin` , `catalog` and `system` folder recursively to OpenCart installation root directory.
2. Navigate to OpenCart Admin Panel, click on `Extensions > Payments` and install `Payabbhi` extension for OpenCart.
3. Click on edit option for `Payabbhi` to configure:
  - [Access ID](https://payabbhi.com/docs/account/#api-keys)
  - [Secret Key](https://payabbhi.com/docs/account/#api-keys)
  - [payment_auto_capture](https://payabbhi.com/docs/api/#create-an-order)
  - `Status` - Change to `Enabled`
4.  Save the Settings.

[Payabbhi Checkout ](https://payabbhi.com/docs/checkout) is now enabled in OpenCart.
