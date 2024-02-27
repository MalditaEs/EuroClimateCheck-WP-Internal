<?php

/**
 * @package
 * @version
 */
/*
Plugin Name: EFCSN ClaimReview EE24 Plugin
Plugin URI: https://github.com/FullFact/claim-review-schema-wordpress-plugin/
Description: WordPress Plugin to implement the EE24 Repository and the ClaimReview Schema. Based on FullFact's ClaimReview Schema Plugin.
Version: 1.0.7
Author: EFCSN
Author URI: https://efcsn.com/
Tags:
License: GPLv2 or later
Text Domain: claimreview, efcsn, factchecking, repository
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

define( 'EE24_PLUGIN_PATH', dirname( __FILE__ ) );
define( 'EE24_PLUGIN_URL', plugins_url( '', __FILE__ ) );

require_once EE24_PLUGIN_PATH . '/inc/core.php';
