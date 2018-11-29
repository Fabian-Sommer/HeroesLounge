
jQuery(function() {

  // We can attach the `fileselect` event to all file inputs on the page
  jQuery(document).on('change', ':file', function() {
    var input = jQuery(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);
  });

  // We can watch for our custom `fileselect` event like this
  jQuery(document).ready( function() {
      jQuery(':file').on('fileselect', function(event, numFiles, label) {

          var input = jQuery(this).parents('.fileselect').find(':text'),
              log = numFiles > 1 ? numFiles + ' files selected' : label;

          if( input.length ) {
              input.val(log);
          } else {
              if( log ) alert(log);
          }

      });

      // Client-size checks for image uploaders
      // Images must be PNG and under 100MB 
      jQuery("input[type='file'][accept='image/png']").on('change', validateImageFileAndSize);

      function validateImageFileAndSize() {
          if (this.files.length === 0) {
              return;
          }
          
          var fieldName = jQuery(this).attr('name');
          var errorDiv = jQuery(`#${fieldName}UploadError`);

          var uploadedFile = this.files[0];
          errorDiv.text("");
          var errors = [];
          if (uploadedFile.type !== "image/png") {
              errors.push("File must be a PNG.");
          }
          if (uploadedFile.size > 104857600) {
              errors.push("Size exceeds 100MB.");
          }

          if (errors.length > 0) {
              errorDiv.text(errors.join(" "));
              jQuery("input.filename").val("");
              this.value = "";
          }
      }
  });
});