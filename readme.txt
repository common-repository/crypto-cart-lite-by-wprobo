=== CryptoCart Lite by WPRobo ===
Contributors: wprobo
Tags: cryptocurrency, crypto, payments, coinpayments, woocommerce, bitcoin, ethereum, litecoin, ripple
Requires at least: 5.6
Tested up to: 6.2.2
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Integrate cryptocurrency payments into your WooCommerce store with CryptoCart Lite. Accept Bitcoin, Ethereum, Litecoin, Ripple, and more.

== Description ==

CryptoCart Lite by WPRobo is a powerful plugin that enables you to accept cryptocurrency payments directly on your WooCommerce store. With seamless integration with CoinPayments, you can easily start accepting Bitcoin, Ethereum, Litecoin, Ripple, and many other popular cryptocurrencies.

**Primary Features:**
- Securely accept cryptocurrency payments through CoinPayments.
- Easily configure cryptocurrency payment gateway settings.
- User-friendly interface for a smooth payment experience.

**Minimum Requirements:**
- WordPress 5.6 or later
- PHP 7.4 or later

== Installation ==

1. Upload the `cryptocart-lite` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to CryptoCart Lite in the WordPress admin dashboard.
4. Enable CoinPayments integration by checking the corresponding box.
5. Save your settings.
6. Navigate to WooCommerce > Settings > Payments > CoinPayments.net - CryptoCart Lite.
7. Enter your CoinPayments information, such as Merchant ID, IPN Secret, etc.
8. Save the settings.

== Frequently Asked Questions ==

= Q1: What cryptocurrencies does CryptoCart Lite support? =
CryptoCart Lite supports a wide range of cryptocurrencies, including but not limited to Bitcoin, Ethereum, Litecoin, Ripple, and more. It seamlessly integrates with CoinPayments, allowing you to accept payments in various popular cryptocurrencies.

= Q2: Is CryptoCart Lite compatible with my WordPress version? =
Yes, CryptoCart Lite is compatible with WordPress version 5.6 or above. We ensure that the plugin stays up-to-date with the latest WordPress releases to provide you with a smooth experience.

= Q3: Does CryptoCart Lite require WooCommerce to function? =
Yes, CryptoCart Lite is designed as an extension for WooCommerce. It requires WooCommerce version 4.0 or above to function properly. If you don't have WooCommerce installed, you will need to install and activate it before using CryptoCart Lite.

= Q4: Can I change the log path? =

Yes, you can change the log path in our plugin using a filter. We have introduced a filter called `wprobo_ccp_functions_add_log_path` that allows you to modify the path of the log file.

Please use the following filter hook:

`wprobo_ccp_functions_add_log_path`




Or check the following snippet:

<a href="https://gist.github.com/infowprobo/4134066c955aaf5e1596de46ba8200c3">https://gist.github.com/infowprobo/4134066c955aaf5e1596de46ba8200c3 </a>

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
   the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
   directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
   (or jpg, jpeg, gif).
2. Screenshot 1: CryptoCart Lite menu in the WordPress admin dashboard.
3. Screenshot 2: CoinPayments.net - CryptoCart Lite settings in WooCommerce.

== License ==

CryptoCart Lite by WPRobo is released under the **GNU General Public License version 3.0 (GPLv3)**.

## License Summary:

- You are free to use, modify, and distribute this plugin, both the original and any modifications you make, as long as you comply with the terms of the GPLv3.
- Any derivative works based on CryptoCart Lite must also be distributed under the GPLv3, ensuring that the entire software ecosystem remains open and freely available to all users.
- You may use CryptoCart Lite for both personal and commercial purposes without any restrictions.

For more details about the GNU General Public License version 3.0, please visit the [GNU website](https://www.gnu.org/licenses/gpl-3.0.html).

== Support ==

For any questions or support, please refer to our [Documentation](https://wprobo.com/plugins/cryptocart-lite/) or reach out to our support team at [info@wprobo.com](mailto:info@wprobo.com).

== Examples and Usage Demos ==

We are excited to showcase the capabilities of CryptoCart Lite by WPRobo through live examples and usage demos. Explore the following demo to see how CryptoCart Lite seamlessly integrates with WordPress and WooCommerce to enable smooth cryptocurrency payment processing:

**Demo URL**: [CryptoCart Lite Demo](https://lite-demo.cryptocartpro.com)

In the demo, you can experience firsthand how CryptoCart Lite works on a live website, allowing you to understand its features and functionalities in action.

Please note that the demo is for demonstration purposes only and may not reflect the latest version of the plugin. For the most up-to-date features and improvements, we recommend downloading and installing the latest version of CryptoCart Lite from the [WordPress plugin repository](https://wordpress.org/plugins/cryptocart-lite/).

We hope you enjoy exploring the demo and see how CryptoCart Lite can enhance your WordPress website by enabling secure and efficient cryptocurrency payment processing.

== Known Issues and Limitations ==

We want to make users aware of the following known issue and limitation in CryptoCart Lite:

1. **IPN Secret Restrictions**: When setting up your CoinPayments account with CryptoCart Lite, please note that the IPN (Instant Payment Notification) Secret should not contain any special characters, such as `!@#$%^&*()_+` or any other non-alphanumeric symbols. Including special characters in the IPN Secret can lead to issues with the processing of payment notifications.

To ensure smooth communication between CryptoCart Lite and CoinPayments, we recommend using only alphanumeric characters (letters and numbers) in the IPN Secret field.

If you encounter any other issues or have questions related to the plugin, please don't hesitate to contact our support team at [info@wprobo.com](mailto:info@wprobo.com). We appreciate your understanding and support as we continue to improve CryptoCart Lite for a better user experience.

== Changelog ==

= 1.0.1 =
* Plugin URL change in plugin header.

= 1.0.0 =
* Initial release of CryptoCart Lite.

== Upgrade Notice ==

= 1.0.0 =
* Initial release.
