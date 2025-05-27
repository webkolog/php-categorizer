# W-PHP Categorizer

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**Version:** 1.1 (Categorizer)

**Last Updated:** 2016-08-26

**Compatibility:** PHP 5.4

**Created By:** Ali Candan ([@webkolog](https://github.com/webkolog))

**Website:** [http://webkolog.net](http://webkolog.net)

**Copyright:** (c) 2015 Ali Candan

**License:** MIT License ([http://mit-license.org](http://mit-license.org))


**W-PHP Categorizer** is a PHP class designed to easily manage and display hierarchical data, such as categories, in a tree-like or nested structure. It provides functionalities to fetch data from a database table, organize it based on parent-child relationships, and present it in various formats suitable for navigation menus, select dropdowns, or breadcrumb trails.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
    - [Basic Initialization](#basic-initialization)
    - [Configuration Options](#configuration-options)
    - [Fetching and Organizing Categories](#fetching-and-organizing-categories)
    - [Accessing Category Lists](#accessing-category-lists)
    - [Displaying Categories in a Tree Structure (HTML List)](#displaying-categories-in-a-tree-structure-html-list)
    - [Displaying Categories in a Nested Structure (Path)](#displaying-categories-in-a-nested-structure-path)
    - [Generating a Select Dropdown with Indentation](#generating-a-select-dropdown-with-indentation)
- [Example Usage](#example-usage)
- [License](#license)
- [Contributing](#contributing)
- [Support](#support)

## Features

- **Database Interaction:** Fetches hierarchical data from a specified database table using PDO.
- **Customizable Table and Column Names:** Allows you to configure the names of the table, ID column, parent ID column, and name column.
- **Ordering:** Supports specifying an order column and order type (ASC/DESC) for sorting categories.
- **Filtering:** Enables filtering categories based on a custom SQL WHERE clause and bound parameters.
- **Extra Columns:** Allows fetching and including additional columns from the database table in the category data.
- **Tree Structure Output:** Generates a flat array representing the categories in a tree-like structure with depth information.
- **Nested Structure Output:** Creates an array representing the path from the selected category to the top-level parent.
- **Selected Item Tracking:** Facilitates marking a specific category as "selected" for use in forms or navigation.

## Requirements

- PHP 5.4 or higher (due to the original code's compatibility note)
- PDO (PHP Data Objects) extension enabled
- A database connection established using PDO

## Installation

1. **Download the `categorizer.php` file:** You can download the `categorizer.php` file directly from your repository.
2. **Include the class in your PHP project:** Use `require_once` or `include_once` to include the `categorizer.php` file in your PHP script where you intend to use it.

   ```php
   require_once 'categorizer.php';
   ```
   
## Usage

### Basic Initialization

To start using the Categorizer class, you need to instantiate it by passing your PDO database connection object as an argument.

```php
// Assuming you have a PDO database connection established in a variable named $db
$nestedCat = new Categorizer($db);
```
### Configuration Options

After initializing the class, you need to configure it by setting its public properties according to your database table structure and desired behavior.

- $tableName **(string):** The name of the database table that holds your category data.
```php
$nestedCat->tableName = "your_categories_table";
```
- $colId **(string):** The name of the column in your table that uniquely identifies each category (e.g., id, category_id). Default: "id".
```php
$nestedCat->colId = "category_id";
```
- $colTop **(string):** The name of the column that stores the ID of the parent category. Use 0 or NULL for top-level categories (depending on your database design). Default: "top_id".
```php
$nestedCat->colTop = "parent_id";
```
- $colName **(string):** The name of the column that contains the display name or title of each category. Default: "name".
```php
$nestedCat->colName = "category_name";
```
- $listRow **(string|null):** The name of the column to use for ordering the categories within each level. Set to null if no specific ordering is required. Default: null.
```php
$nestedCat->listRow = "sort_order";
```
- $orderType **(string|null):** The order type to use with $listRow. Can be "ASC" (ascending) or "DESC" (descending). Only applicable if $listRow is set. Default: null.
```php
$nestedCat->orderType = "ASC";
```
- $selectedId **(int):** The ID of the category that should be marked as "selected" in the $treeList. This is useful for highlighting the current category or pre-selecting an option in a form. Default: 0.
```php
$nestedCat->selectedId = 15;
```
- $extraCols **(array):** An array of strings, where each string is the name of an additional column from your table that you want to include in the category data in both $treeList and $nestedList. Default: array().
```php
$nestedCat->extraCols = array("slug", "description");
```
- $filter **(string|null):** A SQL WHERE clause (without the WHERE keyword) to filter the categories fetched from the database. You can use placeholders (? or named placeholders) for values. Default: null.
```php
$nestedCat->filter = "is_active = ?";
```
- $filterValues **(array):** An array of values to bind to the placeholders in the $filter clause. The order of values should correspond to the order of placeholders. Default: array().
```php
$nestedCat->filterValues = array(1); // For the example filter "is_active = ?"
```
### Fetching and Organizing Categories
Once you have configured the class, you need to call the makeCategorize() method to fetch the data from the database and organize it into the desired structures.
```php
$nestedCat->makeCategorize();
```
### Accessing Category Lists
After calling makeCategorize(), the class will have two public properties containing the organized category data:

- $treeList (array): An array representing the categories in a flattened tree structure. Each element in the array is an associative array with the following keys:
  - "id": The ID of the category.
  - "top": The ID of the parent category.
  - "name": The name of the category.
  - "depth": The depth level of the category in the hierarchy (0 for top-level).
  - "selected": A boolean value indicating whether this category's ID matches the $selectedId.
  - Any additional keys corresponding to the column names specified in $extraCols.
- $nestedList (array): An array representing the path from the $selectedId category up to the top-level parent categories. Each element is an associative array with the following keys:
  - "id": The ID of the category.
  - "top": The ID of the parent category.
  - "name": The name of the category.
  - Any additional keys corresponding to the column names specified in $extraCols. The order of elements in this array is from the selected category upwards to the root.

### Displaying Categories in a Tree Structure (HTML List)
You can iterate through the $treeList to display the categories in a nested HTML list (<ul> and <li> tags), visually representing the hierarchy using the "depth" information.
```php
echo "<ul>\n";
$currentDepth = 0;
foreach ($nestedCat->treeList as $category) {
    if ($currentDepth < $category["depth"]) {
        echo str_repeat("<ul>\n", $category["depth"] - $currentDepth);
    } elseif ($currentDepth > $category["depth"]) {
        echo str_repeat("</ul>\n", $currentDepth - $category["depth"]);
    }
    echo "<li>" . htmlspecialchars($category["name"]) . "</li>\n";
    $currentDepth = $category["depth"];
}
echo str_repeat("</ul>\n", $currentDepth) . "</ul>\n";
```
### Displaying Categories in a Nested Structure (Path)
The $nestedList can be used to display the path of the selected category, often used as a breadcrumb.
```php
echo "Path: ";
$pathParts = array();
foreach ($nestedCat->nestedList as $category) {
    $pathParts[] = htmlspecialchars($category["name"]);
}
echo implode(" &raquo; ", $pathParts);
echo "\n";
```
### Generating a Select Dropdown with Indentation
You can easily create a HTML `<select>` dropdown with indented options to represent the category hierarchy, using the "depth" and "selected" information from $treeList.
```php
echo "<select>\n";
echo "<option value=\"0\">-- Select Category --</option>\n";
foreach ($nestedCat->treeList as $category) {
    echo "<option value=\"" . htmlspecialchars($category["id"]) . "\"";
    if ($category["selected"]) {
        echo " selected";
    }
    echo ">" . str_repeat("- ", $category["depth"]) . htmlspecialchars($category["name"]) . "</option>\n";
}
echo "</select>\n";
```
## Example Usage
```php
<?php

// Assuming you have established a PDO connection
try {
    $db = new PDO('mysql:host=localhost;dbname=your_database;charset=utf8', 'your_username', 'your_password');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

require_once 'categorizer.php';

$nestedCat = new Categorizer($db);
$nestedCat->tableName = "forum_sections";
$nestedCat->colId = "id";
$nestedCat->colTop = "parent_id";
$nestedCat->colName = "section_name";
$nestedCat->listRow = "display_order";
$nestedCat->extraCols = array("slug", "is_hidden");
$nestedCat->filter = "is_hidden = ?";
$nestedCat->filterValues = array(0);
$nestedCat->orderType = "ASC";
$nestedCat->selectedId = 8;
$nestedCat->makeCategorize();

echo "<h3>Tree Structure:</h3>\n";
echo "<ul>\n";
$currentDepth = 0;
foreach ($nestedCat->treeList as $category) {
    if ($currentDepth < $category["depth"]) {
        echo str_repeat("<ul>\n", $category["depth"] - $currentDepth);
    } elseif ($currentDepth > $category["depth"]) {
        echo str_repeat("</ul>\n", $currentDepth - $category["depth"]);
    }
    echo "<li>" . htmlspecialchars($category["name"]) . " (ID: " . $category["id"] . ")";
    if (isset($category["slug"])) {
        echo " - Slug: " . htmlspecialchars($category["slug"]);
    }
    echo "</li>\n";
    $currentDepth = $category["depth"];
}
echo str_repeat("</ul>\n", $currentDepth) . "</ul>\n";

echo "\n<h3>Nested Path (for selected ID " . $nestedCat->selectedId . "):</h3>\n";
$pathParts = array();
foreach ($nestedCat->nestedList as $category) {
    $pathParts[] = htmlspecialchars($category["name"]);
}
echo implode(" &raquo; ", $pathParts);
echo "\n\n";

echo "<h3>Select Dropdown:</h3>\n";
echo "<select>\n";
echo "<option value=\"0\">-- Select Section --</option>\n";
foreach ($nestedCat->treeList as $category) {
    echo "<option value=\"" . htmlspecialchars($category["id"]) . "\"";
    if ($category["selected"]) {
        echo " selected";
    }
    echo ">" . str_repeat("- ", $category["depth"]) . htmlspecialchars($category["name"]) . "</option>\n";
}
echo "</select>\n";

?>
```
## License
This W-PHP Categorizer class is open-source software licensed under the [MIT license](https://mit-license.org/).
```
MIT License

Copyright (c) 2015 Ali Candan

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## Contributing
Contributions are welcome! If you find any bugs or have suggestions for improvements, please `feel free to open an issue or submit a pull request on the GitHub repository.`

## Support
For any questions or support regarding the W-PHP Categorizer, you can refer to the project's GitHub repository or contact the author.
