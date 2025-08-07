# Controller

The controller’s responsibility is to collect and forward data. 
It should not handle complex logic like full-scale validation or sanitization. 

By keeping sanitization tasks simple, we ensure that the controller stays focused on its primary role—coordinating the flow of data.

## Simple Sanitizations which can be done in the Controllers

- Trim whitespace
- Convert to lower or uppercase
- Check for empty fields
- Round Numbers
- Replace spaces or other characters with specific symbols (e.g., underscores).
- Remove non-numeric characters from phone numbers.
- Validate that email matches a specific domain.
- Replace commas with periods for consistent decimal formatting.

More complex logic, such as deep validation or data transformation, belongs in dedicated services or other layers of the application to maintain clear separation of concerns and keep the controller lightweight and maintainable.