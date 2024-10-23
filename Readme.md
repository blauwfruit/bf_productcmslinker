# bf_productcmslinker

## Overview

Link Products to CMS pages in PrestaShop and display CMS pages inside product pages or products inside CMS pages.
## Installation

### Backoffice Upload
1. Download the module package.
2. Go to the PrestaShop admin panel.
3. Navigate to `Modules` > `Module Manager`.
4. Click on `Upload a module` and select the downloaded package.
5. Install the module.

### Git Submodule
1. Navigate to your PrestaShop project's root directory.
2. Add the Product CMS Linker module as a submodule: `git submodule add https://github.com/blauwfruit/bf_productcmslinker modules/bf_productcmslinker`
3. When you want to pull in the latest version run `git submodule update --init --recursive`

## Usage
### Module Configuration:
1. Navigate to Modules > Module Manager.
2. Find Product CMS Linker and click Configure.
3. Toggle options to enable displaying CMS pages on product pages or vice versa.
4. Save the settings.
### Linking CMS Pages to Products:
1. Go to Catalog > Products.
2. Edit a product.
3. Find the Product CMS Linker section.
4. Search for and select CMS pages to link with the product.
5. Save the changes.

## Docker

For development or demo purposes you can run Docker to test this integration.

For the latest PrestaShop:
```bash
gh repo clone blauwfruit/bf_productcmslinker .
docker compose up
```

For other version

```bash
gh repo clone blauwfruit/bf_productcmslinker .
docker compose down --volumes && export TAG=8.1.7-8.1-apache && docker compose up
```