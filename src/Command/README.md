Command
=======


`Command` is a unit of high level action.
Information may be passed to `Command` via a list of public properties described by list of `Option` definitions.

`Definition` contains list of `Option` alongside with additional `Command` information.

`RequestReader` gets option values from `Request` array of options using `Definition::optionsArray()`.

`Response` provides methods for returning response data, `Response::error`, `Response::success`, `Response::content`.


`ResponseRenderer` generates output based on `Response` data.

`Runner` is setting up `Command` with `RequestParser` and invoking action.

`Request` is intersected with set of `Option` by `Runner` to retrieve option values and set up `Command`.




 
Parameters can be parsed from `Request` by set of `Option` definitions. 
After that, they are applied to `Command` instance.




