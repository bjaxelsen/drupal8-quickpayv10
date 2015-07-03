# Drupal 8 Quickpay v10 module

## Changelog
#### 03-07-2015
* Removed local transaction table to avoid out-of-sync data with Quickpay
* Renamed quickpay_test to quickpay_example because that’s what it is
* Made Quickpay class a complex configuration entity.
* Updated to work with beta11

## Overview
This module works with the Danish payment gateway provider QuickPay.dk v10
allowing you to receive payments using the payment options supported
by QuickPay on your Drupal 8 site.

This module is pretty much a port from the existing Quickpay module for Drupal 7.

## Warning
This is a first draft. As Drupal 8 develops things might change and break.
Also there are a couple @TODOs around, but consider this a good starting point.

While this module are as plug and play as is possible, setting up
online payment requires some insight into Drupal, payment gateways and
PBS requirements, and is not for the casual user. If in doubt, consult
a professional.

## Usage
Look at the test module for an example of how to use.

## Authors / Maintainers.
Drupal 8 version  
Kristian Kaa (kaa4ever on Drupal.org)  
kaakristian@gmail.com

Drupal 7 version  
Thomas Fini Hansen (aka Xen on Drupal.org)  
xen at xen dot dk
