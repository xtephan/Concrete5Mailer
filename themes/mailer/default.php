<?php
/**
 * default.php
 * (C) stefanfodor @ 2014
 * SF
 */
defined('C5_EXECUTE') or die("Access Denied.");

//in default cases, we include the page type created by the user
global $ct_key;
include( DIR_BASE . DIRECTORY_SEPARATOR . 'page_types' . DIRECTORY_SEPARATOR . $ct_key . '.php');
