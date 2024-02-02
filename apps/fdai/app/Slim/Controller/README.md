About this directory:
=====================

TODO: What distinguishes the files in this folder from those in the other folders?

Controller - The "C" in "MVC"

The Controller's job is to translate incoming requests into outgoing responses.

In order to do this, the controller must take request data and pass it into the Service layer.

The service layer then returns data that the Controller injects into a View for rendering.

This view might be HTML for a standard web request; or, it might be something like JSON (JavaScript Object Notation) for
a RESTful API request.

See:
http://si.ua.es/en/documentacion/asp-net-mvc-2/imagenes/introduction/flow-mvc.gif
