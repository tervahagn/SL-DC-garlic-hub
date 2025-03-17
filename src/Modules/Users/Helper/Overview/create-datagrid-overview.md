# How to create a DataGrid for Overview Site

Unfortunately this is a complex topic. You had to some classes as there are parts which can be created generic and some parts needs many conditions.
Lets's see if we can handle this hasslefree.

## Controller
Create ShowOverviewController.php in the Controller directory of the module.
To keep the number of dependencies low, only a facade is required.

## Required Files and Directories
It is recommended to follow this way to have as less work as possible 

### Directories
You should create an Overview dir in the Helper dir of the module 

### Parameter Class
Create a `Parameter`-class derivated from [BaseFilterParameters](../../../../Framework/Utils/FormParameters/BaseFilterParameters.php)
This is required to sanitize user inputs. You can set also the parameters according to the rights handled in AclValidator
BaseFilterParameters requiere [Sanitizer.php](../../../../Framework/Core/Sanitizer.php) and [Session.php](../../../../Framework/Core/Session.php) a module name and a sessionstorage name.
the session storage will store the last filter values of the user.

### FormCreator Class
Create a FormCreator-Class witch required at uses at least the Parameter-class, the [Translator](../../../../Framework/Core/Translate/Translator.php), and 
the [FormBuilder](../../../../Framework/Utils/Html/FormBuilder.php) as Constructor-injection
This class will create the form elements depending on the settings of the Parameter Class.

### Template and TemplateRenderer
In the template/module-dir create an overview.mustache file and integrate
[filter.mustache](../../../../../templates/generic/filter.mustache) and 
[results.mustache](../../../../../templates/generic/results.mustache) from generic dir.

This will give you the opportunity to integrate additionally HTML like Context menus, javascript etc
Create the corresponding TemplateRenderer class. This class will also need the Parameter and [Translator.php](../../../../Framework/Core/Translate/Translator.php) injected by the constructor.

### ResultsManager
This class will prepare and render the data for the template.

### Facade 
Create a Facade Class derivated from interface [DataGridFacadeInterface](../../../../Framework/Utils/DataGridFacadeInterface.php)
This will be the only dependency you give to the ShowOverviewController.