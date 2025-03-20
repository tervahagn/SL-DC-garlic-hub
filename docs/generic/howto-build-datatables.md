# How to create a Datatable for Modules

The datatable component display all units of a module. E.g. player, user, playlists, templates etc.
It is the standard component which should be displayed when module name only is in the url route.

JavaScript is not used to generate datatables. Most free client based datatables scripts needs the complete table from the database.
The garlic-hub datatables should be able to handle even huge amounts of data like logs. This means our datatables request only the paginated parts from database. 

In the future there will be asyncronous updates. Currently, every click reloads the complete site.

**Remark: the Software is in status of developing. Docs can change**

Unfortunately this is a complex topic. You had to create four helper classes plus the controller as there are only partly generic functionalities. Especially the list elements part is mostly individual. 

## Required Files and Directories
It is recommended to follow the scripted way and use name the classes identically to make it as easy as possible.

### Controller
Create ShowDatatableController.php in the Controller directory of the module.
To keep the number of dependencies low, only the [DatatableFacadeInterface](../../src/Framework/Utils/Datatable/DatatableFacadeInterface.php)facade and [DataGridTemplateFormatter.php](../../src/Framework/Utils/Datatable/DataGridTemplateFormatter.php) are required.

### Directories
Create a Datatable dir in the Helper dir of the module. 

### Parameter Class
Derivate a `Parameter` class from [BaseFilterParameters](../../src/Framework/Utils/FormParameters/BaseFilterParameters.php) to sanitizes and handles user inputs.
You can set also the parameters according to the rights handled in modules `AclValidator`
BaseFilterParameters require [Sanitizer.php](../../src/Framework/Core/Sanitizer.php) and [Session.php](../../src/Framework/Core/Session.php) a module name and a sessio nstorage name.
The session storage will store the last entered filter values of the user.

### DatatableBuilder Class

The  `DatatableBuilder` is responsible for creating a data grid (Datatable). It supports the creation of form elements, table columns, pagination and dropdown elements for the data view.

Create a DatatableBuilder-Class witch required at least the
[BuildServiceLocator](../../src/Framework/Utils/Datatable/BuildServiceLocator.php), the Parameter-class, and optional the [Translator](../../src/Framework/Core/Translate/Translator.php) as Constructor-injection

### DatatableFormatter Class

The `DatatableFormatter` class provides functionality for formatting data grids including filter forms, pagination, table headers, and table body.
Create a DatatableBuilder-Class witch required at least the [FormatterServiceLocator](../../src/Framework/Utils/Datatable/FormatterServiceLocator.php)
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
Create a Facade Class derivated from interface [DatatableFacadeInterface](../../src/Framework/Utils/DatatableFacadeInterface.php)
This will be the only dependency you give to the ShowOverviewController.