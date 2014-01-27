/* global $:false */
/* global jQuery:false */
/* global FB:false */

function _(id) {
  return $(document.getElementById(id)).html();
}

var EbFacebook = function () {
  var _this = this;
  this.permissions = '';

  $(document).ready(function () {
    _this.init();
  });
};

EbFacebook.prototype.setPermissions = function (permissions) {
  this.permissions = permissions;

  return this;
};

EbFacebook.prototype.init = function () {
  this.pathRouteInSession = $('#pathRouteInSession').val();
  this.initFb();
  this.initUserForm();
  this.initPopin();
};

EbFacebook.prototype.addError = function (e, type, text) {
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
};

EbFacebook.prototype.initPopin = function () {
  var popin = $('.popin');
  if (popin.hasClass('auto-open')) {
    popin.show();
  }
};

EbFacebook.prototype.initUserForm = function () {
  this.initUserFormErrors();
  this.initUserFormAsterisks();
};

EbFacebook.prototype.initUserFormErrors = function () {
  var _this        = this;
  var user_form   = $('.user-form');
  var inputs      = $('input.required:not([type=submit])', user_form);

  user_form.submit(function () {
    $('.qtip').remove();
    var error = false;

    inputs.each(function () {
      var e    = $(this);
      var type = e.attr('type');
      if ((type !== 'checkbox' && e.val() === '') ||
        (type === 'checkbox' && !e.is(':checked'))) {
        _this.addError(e, type);
        error = true;
      } else {
        if (e.attr('pattern')) {
          var pattern = e.attr('pattern');
          if (pattern.substring(0, 2) !== '.*') pattern = '^' + pattern + '$';

          var reg = new RegExp(pattern);
          var val = e.val();

          if (!reg.test(val)) {
            _this.addError(e, type, _('eb_facebook.invalid'));
            error = true;
          }
        }
      }
    });

    if (error) return false;
  });
};

EbFacebook.prototype.initUserFormAsterisks = function () {
  var user_form   = $('.user-form');
  if (!user_form.data('noasterisk')) {
    $('.required', user_form).each(function () {
      var element = $(this).parents('.form-element');
      if (!element.hasClass('noasterisk')) {
        var label = element.find('label');
        label.html(label.html() + ' *');
      }
    });
  }
};

EbFacebook.prototype.initFb = function () {
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
    if ($(this).data('picture')) obj.picture = $(this).data('picture');
    FB.ui(obj, function () { });
  });
  $('.fbInvit').click(function (e) {
    e.preventDefault();
    var url = $(this).data('path');
    var message = $(this).data('message');
    FB.ui({
      method: 'apprequests',
      message: message
    }, function (response) {
      if (response && response !== null) {
        $.ajax({
          dataType: 'json',
          url: url,
          data: {to: response.to},
          method: 'POST',
          success: function (res) {
            if (res > 0) {
              window.location.reload();
            } else {
              window.alert(_('layout.invitation'));
            }
          }
        });
      }
    });
  });
};

EbFacebook.prototype.saveReferer = function (data, next) {
  var _this = this;
  if (typeof data === 'object') {
    data.direct_url = data.direct_url || null;
    data.route = data.route || null;
    data.route_params = data.route_params || null;

    $.ajax({
      type: 'POST',
      url: _this.pathRouteInSession,
      data: {
        direct_url: data.direct_url,
        route: data.route,
        route_params: data.route_params
      }
    }).done(function (data) {
      if (data && data.res && typeof next === 'function') return next(data);
      else noty({ text: 'Une erreur est survenue', type: 'warning', layout: 'top' });
    });

  } else if (typeof next === 'function') return next();
};

EbFacebook.prototype.ebLogin = function (refererData) {
  var _this = this;

  var login = function (n) {
    FB.login(
      function () {
        if (typeof n !== 'undefined') return n();
      }, { scope: _this.permissions }
    );
  };

  if (refererData) {
    _this.saveReferer(refererData, function () {
      login();
    });
  } else login();
};

EbFacebook.prototype.initLoginBtns = function () {
  var _this = this;

  $('.ebLogin').click(function (e) {
    e.preventDefault();
    var refererData = false;
    var route = $(this).data('route');
    var route_params = $(this).data('route_params');
    var direct_url = (!route && !route_params && $(this).attr('href') !== '#') ? $(this).attr('href') : false;

    if (route || route_params || direct_url) {
      refererData = {
        direct_url: direct_url,
        route: route,
        route_params: route_params
      };
    }

    _this.ebLogin(refererData);
  });
};

if (typeof jQuery !== 'undefined') {
  var ebFacebook = new EbFacebook();
}