function _(id) {
    return $(document.getElementById(id)).html();
};

var ebFacebook = {

    init:function(){
        this.initFb();
        this.initUserForm();
        this.initPopin();
    },
    addError: function (e, type, text) {
        if (typeof text === 'undefined') {
            text = type === 'checkbox' ? _('eb_facebook.check') : _('eb_facebook.required');
        }
        e.qtip({
            content: {
                text: text
            },
            position: {
                my: type === 'checkbox' ? 'bottom center' : 'center left',
                at: type === 'checkbox' ? 'top center' : 'center right',
                container: $('#top-wrapper'),
                adjust: {
                    x: 0,
                    y: 0
                }
            },
            show: {
                ready: true,
                event: false
            },
            style: {
                classes: 'qtip-red qtip-rounded'
            },
            hide: {
                event: 'focus'
            }
        });
    },
    initPopin: function () {
        var popin = $('.popin');
        if (popin.hasClass('auto-open')) {
            popin.show();
        }
    },
    initUserForm: function () {
        this.initUserFormErrors();
        this.initUserFormAsterisks();
    },
    initUserFormErrors:function(){
        var self        = this;
        var user_form   = $(".user-form");
        var inputs      = $("input.required:not([type=submit])",user_form);
        
        user_form.submit(function(){
            $('.qtip').remove();
            var error = false;
            
              inputs.each(function(){
                var e    = $(this);
                var type = e.attr('type');
                if( (type !== 'checkbox' && e.val() === '') ||
                    (type === 'checkbox' && !e.is(':checked')) ){
                    self.addError(e, type);
                    error = true;
                } else {
                    if (e.attr('pattern')) {
                        var pattern = e.attr('pattern');
                        if (pattern.substring(0,2) !== '.*') pattern = '^' + pattern + '$';

                        var reg = new RegExp(pattern);
                        var val = e.val();
                        
                        if (!reg.test(val)) {
                            self.addError(e, type, _('eb_facebook.invalid'));
                            error = true;
                        }
                    }
                }
            });
                        
            if(error) return false;
        });

    },
    initUserFormAsterisks: function () {
        var user_form   = $(".user-form");
        if (!user_form.data('noasterisk')) {
            $('.required', user_form).each(function() {
                var element = $(this).parents('.form-element');
                if (!element.hasClass('noasterisk')) {
                    var label = element.find('label');
                    label.html(label.html() + ' *');
                }
            });
        }
    },
    initFb:function(){
        $('.fbLogin').click(function (e) {
            e.preventDefault();
            fbLogin();
        });
        $('.fbPostWall').click(function (e) {
            e.preventDefault();
            var obj = { method: 'feed' };
            if ($(this).data('caption')) obj.caption = $(this).data('caption');
            if ($(this).data('description')) obj.description = $(this).data('description');
            if ($(this).data('link')) obj.link = $(this).data('link');
            FB.ui(obj, function(response){});
        });
        $('.fbInvit').click(function(e){
            e.preventDefault();
            var url = $(this).data('path');
            var message = $(this).data('message');
            FB.ui({
                method: 'apprequests',
                message: message
            }, function(response){
                if(response && response !== null){
                    $.ajax({
                        dataType: "json",
                        url: url,
                        data: {to:response.to},
                        method: "POST",
                        success: function(res) {
                            if(res > 0){
                                window.location.reload();
                            } else {
                                alert(_('layout.invitation'));
                            }
                        }
                    });
                }
            });
        });
    }
};

if (typeof jQuery !== 'undefined') {
    $(document).ready(function(){
        ebFacebook.init();
    });
}