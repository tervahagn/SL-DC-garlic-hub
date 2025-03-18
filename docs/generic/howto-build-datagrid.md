# How to create a DataGrid for Overview Site

**Remark: the Software is in status of developing.Dovcs can change**

Unfortunately this is a complex topic. You had to create some classes as there are only partly generic functionalities. Especially the list part is mostly individual. 

Let's see if we can handle this most hassle free.

## Required Files and Directories
It is recommended to follow the scripted way and use sameclass names to make it as easy as possible.


### Controller
Create ShowOverviewController.php in the Controller directory of the module.
To keep the number of dependencies low, only a facade and a template formatter are required.

### Directories
Create an Overview dir in the Helper dir of the module. 

### Parameter Class
Create a `Parameter` class derivated from [BaseFilterParameters](../../src/Framework/Utils/FormParameters/BaseFilterParameters.php)

This class sanitizes and handles user inputs. You can set also the parameters according to the rights handled in modules `AclValidator`
BaseFilterParameters require [Sanitizer.php](../../src/Framework/Core/Sanitizer.php) and [Session.php](../../src/Framework/Core/Session.php) a module name and a sessio nstorage name.
The session storage will store the last entered filter values of the user.

### DataGridBuilder Class

The  `DataGridBuilder` is responsible for creating a data grid (DataGrid). It supports the creation of form elements, table columns, pagination and dropdown elements for the data view.

Create a DataGridBuilder-Class witch required at least the
[BuildServiceLocator](../../src/Framework/Utils/DataGrid/BuildServiceLocator.php), the Parameter-class, and optional the [Translator](../../src/Framework/Core/Translate/Translator.php) as Constructor-injection

### DataGridFormatter Class

The `DataGridFormatter` class provides functionality for formatting data grids including filter forms, pagination, table headers, and table body.
Create a DataGridBuilder-Class witch required at least the [FormatterServiceLocator](../../src/Framework/Utils/DataGrid/FormatterServiceLocator.php)
the modules **AclValidator** and the [Translator](../../src/Framework/Core/Translate/Translator.php) as constructor injection.

### Template and TemplateRenderer
In the template/module-dir create an overview.mustache file and integrate
[filter.mustache](../../../../../templates/generic/filter.mustache) and 
[results.mustache](../../../../../templates/generic/results.mustache) from generic dir.

This will give you the opportunity to integrate additionally HTML like Context menus, javascript etc
Create the corresponding TemplateRenderer class. This class will also need the Parameter and [Translator.php](../../src/Framework/Core/Translate/Translator.php) injected by the constructor.

### ResultsManager
This class will prepare and render the data for the template.

### Facade 
Create a Facade Class derivated from interface [DataGridFacadeInterface](../../src/Framework/Utils/DataGridFacadeInterface.php)
This will be the only dependency you give to the ShowOverviewController.