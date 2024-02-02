About this directory:
=====================

TODO: What distinguishes the files in this folder from those in the other folders?

Wrapper around storage layer.

Model - The "M" in "MVC"

The Model's job is to represent the problem domain, maintain state, and provide methods for accessing and mutating the
state of the application.

The Model layer is typically broken down into several different layers:

Service layer - this layer provides cohesive, high-level logic for related parts of an application. This layer is
invoked directly by the Controller and View helpers.

Data Access layer - (ex. Data Gateway, Data Access Object) this layer provides access to the persistence layer. This
layer is only ever invoked by Service objects. Objects in the data access layer do not know about each other.

Value Objects layer - this layer provides simple, data-oriented representations of "leaf" nodes in your model hierarchy.

See:
http://si.ua.es/en/documentacion/asp-net-mvc-2/imagenes/introduction/flow-mvc.gif
