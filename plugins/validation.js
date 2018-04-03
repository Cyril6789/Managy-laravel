// ! Validation Options
// - Set validation options
// - Options:
//    - options: object
//        The options to set
//
// - Note:
//    - If submitHandler is set and
//      returns false, the original
//      submitHandler won't be executed.

(function($, window, undefined){

    $.fn.validationOptions = function(options) {

        var $this = $(this);

        $$.registry.validation(function() {

            return $this.each(function() {

                // Get validation engine
                var $form = $(this),
                    validator = $form.validate();

                // Handle submitHandler
                if (options.submitHandler) {
                    // Store original submitHandler and given submitHandler
                    var _submitHandler = validator.settings.submitHandler,
                        submitHandler = options.submitHandler;

                    // Set submitHandler
                    validator.settings.submitHandler = function(){
                        !!submitHandler.apply(this, arguments) && _submitHandler.apply(this, arguments);
                    };

                    delete options.submitHandler;
                }

                // Handle invalidHandler
                if (options.invalidHandler) {
                    $form.on('invalid-form.validate', options.invalidHandler);
                }

                // Expand settings
                $.extend(validator.settings, options);

            });

        }); // End of '$el.each(function ...)'

    }; // End of '$.fn.setValidationOptions = ...'

})(jQuery, this);

/**
 * Created by Cyril on 02/08/2016.
 */
