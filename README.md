# RT Build System

A WordPress plugin for managing and documenting reusable UI components with an integrated design system approach.

## Description

RT Build System allows developers to create, manage, and document reusable UI components within WordPress. Each component includes code examples, library dependencies, design references, and live previews, making it perfect for maintaining design systems and component libraries.

## Features

-   **Component Management**: Create and organize reusable UI components
-   **Live Previews**: See components in action with dummy data
-   **Code Documentation**: Multiple code formats (HTML, CSS, JS, PHP) with syntax highlighting
-   **Library Tracking**: Monitor external dependencies and their maintenance status
-   **Design Integration**: Link to Figma designs and other references
-   **Documentation System**: Hierarchical documentation pages
-   **Multilingual Support**: Ready for translation (French included)
-   **Download Components**: Export components as ZIP files

## Installation

1. Download the plugin
2. Upload to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to 'Build System' in the admin menu

## Usage

### Creating Components

1. Go to **Build System > Components > Add New**
2. Enter component title and description
3. Add libraries used (name and repository URL)
4. Include design references and documentation links
5. The plugin automatically creates the component directory structure

### Component Structure

Each component includes:

-   `class.php` - Component logic and configuration
-   `template.php` - HTML template
-   `style.css` - Component styles
-   `script.js` - Component JavaScript
-   `thumbnail.{ext}` - Preview image

### Documentation

Create documentation pages under **Build System > Documentation** to provide guides, tutorials, and system documentation.

## Folder Structure

```
├── assets/                     // Plugin assets
│   ├── css/                    // Compiled CSS files
│   ├── js/                     // JavaScript files
│   ├── scss/                   // SCSS source files
│   └── svg/                    // SVG icons
├── components/                 // Component library
│   └── [component-name]/       // Individual component folders
│       ├── class.php           // Component class
│       ├── template.php        // HTML template
│       ├── style.css           // Component styles
│       ├── script.js           // Component JavaScript
│       └── thumbnail.*         // Preview image
├── docs/                       // Documentation XML files
├── includes/                   // Core plugin classes
│   ├── admin.php               // Admin interface
│   ├── plugin.php              // Main plugin class
│   └── tool.php                // Utility functions
├── languages/                  // Translation files
├── templates/                  // Plugin templates
│   ├── component/              // Component template files
│   ├── component.php           // Single component view
│   ├── components.php          // Components archive
│   ├── documentation.php       // Documentation view
│   └── navigation.php          // Navigation template
├── gulpfile.mjs                // Build configuration
├── package.json                // Node dependencies
└── rt-build-system.php         // Main plugin file
```

## Development

### Requirements

-   WordPress 5.0+
-   PHP 7.4+
-   Node.js (for asset compilation)

### Setup

1. Clone the repository
2. Run `npm install` to install dependencies
3. Use `gulp` or `gulp build` to compile SCSS and minify assets

### Creating Custom Components

Components extend the base `Component` class and should implement:

```php
class MyComponent extends Component {
    const NAME = 'my-component';

    public static $dummyData = [
        // Sample data for preview
    ];

    const CODES = [
        'html' => ['label' => 'HTML', 'file' => 'template.php', 'lang' => 'html'],
        'css' => ['label' => 'CSS', 'file' => 'style.css', 'lang' => 'css'],
        'js' => ['label' => 'JavaScript', 'file' => 'script.js', 'lang' => 'javascript'],
    ];
}
```

## API Endpoints

-   `admin-ajax.php?action=rtbs_download_zip&slug={component}` - Download component ZIP

## Translation

The plugin is translation-ready with French translations included. To add your language:

1. Copy `languages/rt-build-system.pot`
2. Translate using tools like Poedit
3. Save as `rt-build-system-{locale}.po`

## Author

**Theo Rige**

-   Website: [rigetheo.netlify.app](https://rigetheo.netlify.app)
-   GitHub: [Theo-Rige/wp-build-system](https://github.com/Theo-Rige/wp-build-system)

## License

This project is licensed under the GPL v2 or later.

## Changelog

### 0.1.0

-   Initial release
-   Component management system
-   Documentation framework
-   Library dependency tracking
-   Multi-language support
