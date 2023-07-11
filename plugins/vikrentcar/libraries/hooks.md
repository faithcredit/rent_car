# Here is the list of all the HOOKS triggered by the plugin

## PAYMENTS

#### payment_before_admin_params_vikrentcar
`do_action('payment_before_admin_params_vikrentcar', JPayment $payment)`

##### Parameters
- **$payment** (JPayment)
The payment object that has been instantiated (passed by reference).

##### Description
Plugins can manipulate the properties of this object.
Fires before the configuration array is generated.

`@since 1.0.6`

---

#### payment_after_admin_params_vikrentcar
`do_action('payment_after_admin_params_vikrentcar', JPayment $payment, array $config)`

##### Parameters
- **$payment** (JPayment)
The payment object that has been instantiated (passed by reference).
- **$config** (array)
The configuration array (passed by reference).

##### Description
Plugins can manipulate the configuration form of the payment.
Fires after generating the form that will be used as configuration.

`@since 1.0.6`

---

#### payment_before_begin_transaction_vikrentcar
`do_action('payment_before_begin_transaction_vikrentcar', JPayment $payment)`

##### Parameters
- **$payment** (JPayment)
The payment object that has been instantiated (passed by reference).

##### Description
Plugins can manipulate the properties of this object.
Fires before the payment form is initiated.

`@since 1.0.6`

---

#### payment_after_begin_transaction_vikrentcar
`do_action('payment_after_begin_transaction_vikrentcar', JPayment $payment, string $html)`

##### Parameters
- **$payment** (JPayment)
The payment object that has been instantiated (passed by reference).
- **$html** (string)
The resulting HTML form (passed by reference).

##### Description
Plugins can manipulate the generated HTML form.
Fires after generating the HTML payment form.

`@since 1.0.6`

---

#### payment_before_validate_transaction_vikrentcar
`do_action('payment_before_validate_transaction_vikrentcar', JPayment $payment)`

##### Parameters
- **$payment** (JPayment)
The payment object that has been instantiated (passed by reference).

##### Description
Plugins can manipulate the properties of this object.
Fires before the payment transaction is validated.

`@since 1.0.6`

---

#### payment_after_validate_transaction_vikrentcar
`do_action('payment_after_validate_transaction_vikrentcar', JPayment $payment, JPaymentStatus $status, mixed $response)`

##### Parameters
- **$payment** (JPayment)
The payment object that has been instantiated (passed by reference).
- **$status** (JPaymentStatus)
The object containing the status information about the transaction (passed by reference).
- **$response** (mixed)
An object containing the final response (passed by reference).
If not manipulated, this value will be NULL.

##### Description
Plugins can manipulate the response object to return.
By filling the `&$response` variable, this method will return it instead of the 
default `&$status` one.
Fires after validating the payment transaction.

`@since 1.0.6`

---

#### payment_on_after_validation_vikrentcar
`do_action('payment_on_after_validation_vikrentcar', JPayment $payment, boolean $res)`

##### Parameters
- **$payment** (JPayment)
The payment object that has been instantiated (passed by reference).
- **$res** (boolean)
The result of the transaction (*true* when verified, *false* on failure).

##### Description
Plugins can manipulate the properties of this object.
Fires before the payment process is completed.

`@since 1.0.6`

---

#### load_payment_gateway_vikrentcar
`do_action_ref_array('load_payment_gateway_vikrentcar', array $drivers, string $payment)`

##### Parameters
- **$drivers** (array) 
A list of supported drivers (passed by reference).
- **$payment** (string)
The name of the gateway to load.

##### Description
Trigger action to obtain a list of classnames of the payment gateway.
The action should autoload the file that contains the classname.
In case the payment should be loaded, the classname MUST be
pushed within the `&$drivers` array.
Fires before the instantiation of the returned classname.

`@since 1.0.5`

---

### get_supported_payments_vikrentcar
`apply_filters('get_supported_payments_vikrentcar', array $drivers)`

##### Parameters
- **$drivers** (array)
An array containing the list of the supported payments.

##### Description
Hook used to filter the list of all the supported drivers.
Every plugin attached to this filter will be able to push one
or more gateways within the `$drivers` array.

It is also possible to manipulate the elements in the array, 
for example to detach a deprecated payment.

`@since 1.0.5`

---

### vikrentcar_oconfirm_payment_logo
`apply_filters('vikrentcar_oconfirm_payment_logo', array $logo)`

##### Parameters
- **$logo** (array)
An array containing the following keys:
`name`	the payment name;
`path`	the payment path;
`uri`	the payment logo image URI.

##### Description
Hook used to filter the array containing the logo's information.
By default, the array contains the standard path and URI, related
to the payment folder of the plugin.

Plugins attached to this hook are able to filter the payment logo in case
the image is stored somewhere else.

`@since 1.0.5`

---

## SYSTEM

#### vikrentcar_before_dispatch
`do_action('vikrentcar_before_dispatch')`

##### Description
Fires before the controller of VikRentCar is dispatched.
Useful to require libraries and to check user global permissions.

`@since 1.0.0`

---

#### vikrentcar_after_dispatch
`do_action('vikrentcar_after_dispatch')`

##### Description
Fires after the controller of VikRentCar is dispatched. Useful to include
web resources (CSS and JS). In case the controller terminates the process
(exit or die), this hook won't be fired.

`@since 1.0.0`

---

#### vikrentcar_before_display_{VIEW}
`do_action('vikrentcar_before_dispatch_' . $view)`

##### Description
Fires before the controller of VikAppointments displays the requested {VIEW}.

`@since 1.2.0`

---

#### vikrentcar_after_display_{VIEW}
`do_action('vikrentcar_after_dispatch_' . $view)`

##### Description
Fires after the controller of VikAppointments has displayed the requested {VIEW}.

`@since 1.2.0`

---

## DATABASE

#### vik_get_db_prefix
`apply_filters('vik_get_db_prefix', string $prefix)`

##### Parameters
- **$prefix** (string)
The database prefix to use for queries.

##### Description
Hook used to filter the default WP database prefix before it is used.

`@since 1.0.6`

---

#### vik_db_suppress_errors 
`apply_filters('vik_db_suppress_errors', boolean $suppress)`

##### Parameters
- **$suppress** (boolean)
True to suppress the errors, false otherwise (false by default).

##### Description
Hook used to suppress/enable database errors.

`@since 1.1.8`

---

#### vik_db_show_errors
`apply_filters('vik_db_show_errors', boolean $show)`

##### Parameters
- **$show** (boolean)
True to show the errors, false otherwise (true by default).

##### Description
In case errors are suppressed, this hook would result useless.
Errors can be shown only if they are NOT suppressed.

`@since 1.1.8`

---

## RESOURCES

#### vik_before_include_script
`apply_filters('vik_before_include_script', boolean true, string $url, string $id, string $version, boolean $footer)`

##### Parameters
- **$load** (boolean)
True to load the resource, false to ignore it.
- **$url** (string)
The resource URL.
- **$id** (string)
The script ID attribute.
- **$version** (string)
The script version, if specified.
- **$footer** (string)
True whether the script is going to be loaded in the footer.

##### Description
Hook used to approve/deny the loading of the given script.

`@since 1.1.1`

---

#### vik_before_include_style
`apply_filters('vik_before_include_style', boolean true, string $url, string $id, string $version)`

##### Parameters
- **$load** (boolean)
True to load the resource, false to ignore it.
- **$url** (string)
The resource URL.
- **$id** (string)
The stylesheet ID attribute.
- **$version** (string)
The stylesheet version, if specified.

##### Description
Hook used to approve/deny the loading of the given stylesheet.

`@since 1.1.1`

---

## WIDGETS

#### vik_widget_before_dispatch_site
`do_action_ref_array('vik_widget_before_dispatch_site', array(string $id, JObject &$params))`

##### Parameters
- **$id** (string)
The widget ID (path name).
- **&$params** (JObject)
The widget configuration registry.

##### Description
Plugins can manipulate the configuration of the widget at runtime.
Fires before dispatching the widget in the front-end.

`@since 1.1.3`

---

#### vik_widget_after_dispatch_site
`do_action_ref_array('vik_widget_after_dispatch_site', array(string $id, string &$html))`

##### Parameters
- **$id** (string)
The widget ID (path name).
- **&$html** (string)
The HTML of the widget to display.

##### Description
Plugins can manipulate the configuration of the widget at runtime.
Fires before dispatching the widget in the front-end.

`@since 1.1.3`
