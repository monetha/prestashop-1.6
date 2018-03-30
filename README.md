# Prestashop

Monetha payment gateway integration with Prestashop 1.5 - 1.6

Detailed install and configuration guide will be available on our website - http://www.monetha.io/en/mvp/

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

### If you have any questions or requests, feel free to ask them via support@monetha.io