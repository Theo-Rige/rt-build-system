Wordpress plugin template, still work in progress.

## Folder structure

```
├── assets/                     // All plugin assets
│   ├── svg/                    // SVG icons files mainly render with Tool::loadSVG()
│   ├── css/                    // Generated minified CSS files from SCSS
│   ├── scss/                   // All SCSS sources files
│   └── js/                     // All plugin JS files, plain and minified
├── includes/                   // Directory of all plugin classes
│   ├── plugin.php              // Main plugin class
│   └── tool.php                // Tool plugin class
├── templates/                  // All template files mainly render with Tool::loadTemplate()
├── .gitignore
├── README.md
├── gulpfile.mjs                // Gulp config file for minify and compile JS and SCSS
├── package.json
├── rt-plugin-template.php      // Main plugin file
```
