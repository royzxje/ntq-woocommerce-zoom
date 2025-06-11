# WooCommerce Zoom Integration

A professional WordPress plugin that automatically creates Zoom meetings when WooCommerce orders are completed and sends meeting details to customers via email.

## 🚀 Features

### Core Functionality
- **Automatic Meeting Creation**: Creates Zoom meetings when orders reach specific status
- **Multiple Zoom Accounts**: Support for multiple Zoom accounts with distribution rules  
- **Smart Distribution**: Assign meetings to different accounts based on products, categories, or custom rules
- **Email Notifications**: Send beautiful HTML and plain text emails with meeting details
- **Meeting Management**: Complete admin interface for managing meetings, accounts, and settings

### Admin Interface
- **Dashboard**: Overview with statistics and recent activity
- **Zoom Accounts Management**: Add, edit, test, and manage multiple Zoom accounts
- **Distribution Rules**: Create rules to assign meetings to specific accounts
- **Activity Logs**: Comprehensive logging system with filtering and export
- **Settings**: Configurable options for meetings, emails, and logging
- **Order Integration**: Meeting information displayed in WooCommerce orders

### Frontend Features
- **Shortcodes**: Display meeting information anywhere on your site
- **Countdown Timer**: Real-time countdown to meeting start
- **Responsive Design**: Mobile-friendly meeting display
- **Access Control**: Login requirements and order verification

### Security & Performance
- **Secure API Integration**: Encrypted storage of API credentials with AES-256
- **JWT Authentication**: Secure Zoom API communication
- **Database Optimization**: Custom tables for optimal performance
- **Multi-language Support**: Translation ready with POT file

## 📋 Requirements

- WordPress 5.0 or higher
- WooCommerce 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Zoom Pro/Business/Enterprise account
- Zoom JWT app credentials

## 📦 Installation

### Method 1: WordPress Admin (Recommended)
1. Download the plugin ZIP file
2. Go to WordPress Admin > Plugins > Add New
3. Click \"Upload Plugin\" and choose the ZIP file
4. Install and activate the plugin

### Method 2: Git Clone
```bash
cd /wp-content/plugins/
git clone https://github.com/royzxje/ntq-woocommerce-zoom.git woocommerce-zoom-integration
```

## ⚙️ Configuration

### 1. Zoom API Setup
1. Go to [Zoom Marketplace](https://marketplace.zoom.us/)
2. Create a JWT app
3. Copy your API Key, API Secret, and Account ID

### 2. Plugin Configuration
1. Go to **WooCommerce > Zoom Integration**
2. Navigate to **Zoom Accounts** tab
3. Add your Zoom API credentials
4. Test the connection

### 3. Distribution Rules
1. Go to **Distribution Rules** tab
2. Create rules to assign meetings to specific Zoom accounts
3. Set conditions based on products, categories, or attributes

### 4. Email Settings
1. Go to **Settings** tab
2. Configure email templates and options
3. Test email delivery

## 🎯 Usage

### Automatic Workflow
1. Customer places an order
2. Order reaches configured status (default: completed)
3. Plugin matches distribution rules
4. Zoom meeting is created automatically
5. Email is sent to customer with meeting details

### Shortcodes
Display meeting information on frontend:

```php
// Basic meeting info
[zoom_meeting_info order_id=\"123\"]

// Join button
[zoom_join_button order_id=\"123\"]

// Countdown timer
[zoom_countdown order_id=\"123\"]
```

### Manual Actions
- **Resend Email**: Resend meeting email from order admin
- **Create Meeting**: Manually create meeting for existing orders
- **View Details**: See meeting information in order meta box

## 🎨 Customization

### Email Templates
Override email templates in your theme:
```
/wp-content/themes/your-theme/woocommerce/emails/zoom-meeting-details.php
/wp-content/themes/your-theme/woocommerce/emails/plain/zoom-meeting-details.php
```

### Frontend Styling
```css
.zoom-meeting-info { /* Your styles */ }
.zoom-join-button { /* Your styles */ }
.zoom-countdown { /* Your styles */ }
```

### Hooks and Filters
```php
// Before meeting creation
do_action('wc_zoom_before_create_meeting', $order_id, $product_id);

// After meeting creation
do_action('wc_zoom_after_create_meeting', $order_id, $meeting_data);

// Filter meeting data
apply_filters('wc_zoom_meeting_data', $meeting_data, $order_id);
```

## 📊 Monitoring

### Activity Logs
Monitor plugin activity in **WooCommerce > Zoom Integration > Logs**
- Meeting creation/deletion events
- API call success/failures
- Email sending status
- Distribution rule matches

### Debug Mode
Enable debug mode in plugin settings for detailed logging.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Make your changes
4. Commit your changes: `git commit -am 'Add new feature'`
5. Push to the branch: `git push origin feature/new-feature`
6. Submit a pull request

## 📝 License

This plugin is licensed under the GPL v2 or later.

## 🔗 Links

- **GitHub**: [https://github.com/royzxje/ntq-woocommerce-zoom](https://github.com/royzxje/ntq-woocommerce-zoom)
- **Author Website**: [https://q2k1.com/](https://q2k1.com/)
- **Support**: Create an issue on GitHub

## 📞 Support

For support, please create an issue on GitHub or contact the developer.

---

**Made with ❤️ for the WordPress community**