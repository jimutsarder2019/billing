<!-- BEGIN: Vendor JS-->
<script src="{{ asset(mix('assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/popper/popper.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/js/bootstrap.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/node-waves/node-waves.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/hammer/hammer.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/libs/typeahead-js/typeahead.js')) }}"></script>
<script src="{{ asset(mix('assets/vendor/js/menu.js')) }}"></script>
<script src="{{asset('assets/vendor/libs/select2/select2.js')}}"></script>
<script src="{{asset('assets/js/forms-selects.js')}}"></script>

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
<script src="{{ asset(mix('assets/js/main.js')) }}"></script>

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->
<script>
    $(document).ready(function() {
        // Find all input elements with a placeholder attribute
        $('input[placeholder]').each(function() {
            // Remove underscores from the placeholder text
            var placeholderText = $(this).attr('placeholder').replace(/_/g, ' ');
            // Set the modified placeholder text
            $(this).attr('placeholder', placeholderText);
        });



        // // Select all label elements using jQuery
        // $("label").each(function() {
        //     // Get the current text of the label
        //     var labelText = $(this).text();
        //     // Replace underscores with spaces in the label text
        //     var modifiedText = labelText.replace(/_/g, ' ');
        //     // Set the modified text as the new text of the label
        //     $(this).text(modifiedText);
        // });

    });
</script>