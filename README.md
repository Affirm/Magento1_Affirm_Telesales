 [![](docs/splash.png)](https://affirm.com) 

Note - This module is an add-on of Affirm's Marketplace Module. To use this module, you must have downloaded and installed Affirm's Marketplace Module first. https://marketplace.magento.com/affirm-affirm-affirm.html

**Compatible with**

Magento CE 1.4.0.1+

Install
-------

####Install via FTP

1. Click here to download the latest package release (.tgz): https://github.com/Affirm/Magento1_Affirm_Telesales/releases/latest
2. Extract the contents of the package to your computer
3. Upload the package contents to your Magento root directory


####To install using from a package (Magento Connect Manager):

1. Click here to download the latest package release (.tgz): https://github.com/Affirm/Magento1_Affirm_Telesales/releases/latest
2. Visit System > Magento Conenct > Magento Connect Manager
3. Upload the Magento1_Affirm_Telesales package

[![](docs/package-connect-menu.png)](https://affirm.com) 
[![](docs/upload.png)](https://affirm.com) 

####To install using [modman](https://github.com/colinmollenhour/modman)

```
cd MAGENTO_ROOT
modman clone https://github.com/Affirm/Magento1_Affirm_Telesales.git
```
to update:
```
modman update Magento1_Affirm_Telesales
```
####To install using [magento-composer-installer](https://github.com/Cotya/magento-composer-installer)
```
composer require affirm/magento1-telesales
```



Configure
---------

Visit https://docs.affirm.com/Integrate_Affirm/Platform_Integration/Magento_Integration#configure_the_affirm payment_method for details


Developers
----------

Read the [Developer README](DEVELOPER-README.md)
