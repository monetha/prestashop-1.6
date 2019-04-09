# Prestashop

Monetha payment gateway integration with Prestashop 1.5 - 1.6

Detailed install and configuration guide is available at https://help.monetha.io/hc/en-us/articles/360002550272-PrestaShop-1-6-integration

Contact email for your questions: team@monetha.io

# Technical guide
1. Go to `"Modules"` in Admin panel, click `Add new module`, select latest zip package from `releases` folder and upload it.
2. Configure module availability, merchant key and merchant secret (click `Configure`).

In order to to try our integration in test mode please make sure to select "Test Mode" and use merchant key and secret provided below:

**Merchant Key:** MONETHA_SANDBOX_KEY

**Merchant Secret:** MONETHA_SANDBOX_SECRET

When test mode is switched on all payment transactions will be made in Ropsten testnet. Make sure not to send money from Ropsten testnet wallet address.

**Possible issues after installation**

Sometimes Smarty templates recompilation could be needed, navigate Advanced Parameters - Performance and click `Clear Cache`.

If you want to create a module from the contents of this repository, perform the actions below:

1. `composer install`
2. create a directory named `monethagateway`
3. copy all the contents from this directory (except `releases` but including `vendor`) inside it
4. create a zip-archive of new `monethagateway` directory's contents.

### If you have any questions or requests, feel free to ask them via support@monetha.io