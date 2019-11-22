Receive payments via M-pesa STK push on magento 2.

Features

1. M-pesa as a payment method during customer checkout 
2. Configure your M-pesa credentials on [Stores -> Configuration -> Sales -> Payments]
3. Live payment feeds on [Reports -> Sales -> Safaricom Mpesa]


Setup:
```
$ cd /var/www/html/magento/app/code
$ git clone https://github.com/BransonGitomeh/Magento-2-Mpesa-Plugin.git Safaricom
$ cd /var/www/html/magento 
$ php bin/magento setup:upgrade && php bin/magento setup:static-content:deploy
```
