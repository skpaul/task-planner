function ValidatePhoto(fileInputId, maximumKB, requiredHeight, requiredWidth){
        
    // debugger;
    var fileName = $("#"+ fileInputId +"").val();

    var title = $("#"+ fileInputId +"").attr("title");

    if(fileName =='')
    {
        $.sweetModal({
            content:  title + " required.",
            icon: $.sweetModal.ICON_WARNING
        });
        //showPhotoError('Please select a photo.');
        return false;
    }

    var fileInput = $("#"+ fileInputId + "")[0];
    var selectedFile = fileInput.files[0];
    
    var regex = /^([a-zA-Z0-9\s_\\.\-:])+(.jpeg|.jpg)$/;

    var arrFileName = fileName.split("\\");

    var fileNameee = arrFileName[arrFileName.length-1]; 
    //fileNameSpan.html(arrFileName[arrFileName.length-1]);

    //check whether it is .jpeg or .jpg ---->
    if (!regex.test(fileName.toLowerCase())) {
        $.sweetModal({
            content: title + " invalid. Please select a .jpg file.",
            icon: $.sweetModal.ICON_WARNING
        });
       // showPhotoError('Please select a .jpg file.');
       return false;
    }
    //<---- check whether it is .jpeg or .jpg

    var fileSizeInByte = selectedFile.size;
    var Units = new Array('Bytes', 'KB', 'MB', 'GB');
    var unitPosition = 0;
    while (fileSizeInByte > 900) {
        fileSizeInByte /= 1024; unitPosition++;
    }

    var finalSize = (Math.round(fileSizeInByte * 100) / 100);
    var finalUnitName = Units[unitPosition];

    var fileSizeAndUnit = finalSize + ' ' + finalUnitName;

    //Check file size ----->
    if (finalUnitName != 'KB') {
        $.sweetModal({
            content: title + " size is too large. Maximum size is 100 kilobytes.",
            icon: $.sweetModal.ICON_WARNING
        });
       // showPhotoError('Photo size is too large. Maximum size is 100 kilobytes.');              
       return false;
    }
    else{
        if(finalSize > maximumKB){ 
            $.sweetModal({
                content: title + " size is too large. Maximum size is 100 kilobytes.",
                icon: $.sweetModal.ICON_WARNING
            });
           // showPhotoError('Photo size is too large. Maximum size is 100 kilobytes.');
           return false;
        }
    }

    /*Checks whether the browser supports HTML5*/
    if (typeof (FileReader) != "undefined") {
        var reader = new FileReader();
        //Read the contents of Image File.
        reader.readAsDataURL(fileInput.files[0]);

        reader.onload = function (e) {
            //Initiate the JavaScript Image object.
            var image = new Image();
            //Set the Base64 string return from FileReader as source.
            image.src = e.target.result;
           
            image.onload = function () {  
                if (this.width != requiredWidth) {
                    $.sweetModal({
                        content: title + " width invalid. Width must be " + requiredWidth + " pixel.",
                        icon: $.sweetModal.ICON_WARNING
                    });
                   // showPhotoError('Invalid photo width. Width must be 300 pixel.');
                   return false;
                }                 
                if (this.height != requiredHeight) {
                    $.sweetModal({
                        content: title + " height invalid. Height must be "+ requiredHeight  + " pixel.",
                        icon: $.sweetModal.ICON_WARNING
                    });
                    //showPhotoError('Invalid photo height. Height must be 300 pixel.');
                    return false;
                }
            };
        }
    }

    return true;
}

$(function(){
    $("#ApplicantPhoto").change(function(){
        var isValid = ValidatePhoto("ApplicantPhoto", 100, 300,300);
        if(isValid){
            var fileInput = this;
            if (fileInput.files && fileInput.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    //$('#photo-preview').attr('src', e.target.result);
                   $('#ApplicantPhotoImage').attr('src', e.target.result).removeClass('hidden');
                }
                reader.readAsDataURL(fileInput.files[0]);
            }
        }
    });



    // var isChecked =  $("input:radio[name='"+inputName+"']").is(":checked");

    function validationRule() {
        // var checked = $('#DeclarationApproval').is(':checked');
        // if (!checked) {
        //     $.sweetModal({
        //         content: 'Please provide your consent in the declaration section.',
        //         icon: $.sweetModal.ICON_WARNING
        //     });
        //     return false;
        // }
        return true;
    }
    
    $('form').swiftSubmit({}, validationRule, null, null, null, null);

}); //Document.ready//