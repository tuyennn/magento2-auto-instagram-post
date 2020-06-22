# Auto Instagram Post - Magento 2
---

This Magento 2 extension Auto Instagram Post allows you add your products immediately to Instagram after publishing it on Magento site, share your thoughts, product information, brand news and latest  to your friends.

[![License: GPL v3](https://img.shields.io/badge/License-GPL%20v3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/bf0757d0063e489eb3bff2479964fce2)](https://www.codacy.com/app/GhoSterInc/AutoInstagramPost?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=tuyennn/AutoInstagramPost&amp;utm_campaign=Badge_Grade)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/thinghost)
[![Build Status](https://travis-ci.org/tuyennn/AutoInstagramPost.svg?branch=master)](https://travis-ci.org/tuyennn/AutoInstagramPost)
![Version 1.1.9](https://img.shields.io/badge/Version-1.1.9-green.svg)

---
## [![Alt GhoSter](http://thinghost.info/wp-content/uploads/2015/12/ghoster.png "thinghost.info")](http://thinghost.info) Overview

- [Extension on GitHub](https://github.com/tuyennn/AutoInstagramPost)
- [Direct download link](https://github.com/tuyennn/AutoInstagramPost/tarball/master)


![Alt Screenshot-1](http://thinghost.info/wp-content/uploads/2017/08/Selection_426-1024x487.jpg "thinghost.info")
![Alt Screenshot-2](http://thinghost.info/wp-content/uploads/2017/08/Selection_424-1024x530.jpg "thinghost.info")
![Alt Screenshot-3](http://thinghost.info/wp-content/uploads/2017/08/Selection_425-1024x456.jpg "thinghost.info")
![Alt Screenshot-4](https://thinghost.info/wp-content/uploads/2015/12/Selection_489.jpg "thinghost.info")
![Alt Screenshot-5](https://thinghost.info/wp-content/uploads/2015/12/Selection_490.jpg "thinghost.info")

## Main Features

* Use Instagram API(Android App Simulation) to post main Product Image of store to Instagram
* Support configurations with #hashtag.
* Support sort content of comment as user defined.
* Support Manage Products Grid mass Action to Post or rePost to Instagram

## Configure and Manage

* Enable Auto Instagram - Enable or disable module.
* Username(Instagram Account) - Your Instagram Username.
* Password - Your Instagram Password.
* Test Connection - Test your current account.
* Default Image - When you add a product without a main Image to store, this image will be uploaded to Insragram.
* Allow auto posting to Instagram after Saving Product - Enable Observer after product saved
* Enable Auto Hashtag and Description - Enable below options while posting product to Instagram.
* Add Product Description to Post - This will add product description to your feature post.
* Add Categories Name as Hashtags - This will add product category as hashtag to your feature post.
* Add Custom Hashtags - Your custom hashtags go there.
* Description Template - This will define the order of content which you want to post.
* Enable Scheduled Auto Post - Setup cron for Scheduled Post to Instagram
* Start Time - Time for Cron.
* Frequency - Frequency for Cron.
* Limit Number of Posts - Limit for 1 time cron runs.

## Installation with Composer

* Connect to your server with SSH
* Navigation to your project and run these commands
 
```bash
composer require ghoster/autoinstagrampost


php bin/magento setup:upgrade
rm -rf pub/static/* 
rm -rf var/*

php bin/magento setup:static-content:deploy
```

## Installation without Composer

* Download the files from github: [Direct download link](https://github.com/tuyennn/AutoInstagramPost/tarball/master)
* Extract archive and copy all directories to app/code/GhoSter/AutoInstagramPost
* Go to project home directory and execute these commands

```bash
php bin/magento setup:upgrade
rm -rf pub/static/* 
rm -rf var/*

php bin/magento setup:static-content:deploy
```

## Contribution

* Fork this repository
* Create your feature branch (`git checkout -b your-new-feature`) always from `develop`
* Commit and Submit a new Pull Request

## Licence

[Open Software License (OSL 3.0)](http://opensource.org/licenses/osl-3.0.php)


## Donation

If this project help you reduce time to develop, you can give me a cup of coffee :) 

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.me/thinghost)
