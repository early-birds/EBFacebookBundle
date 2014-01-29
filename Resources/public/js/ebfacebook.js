/* global $, jQuery:false, FB */

var EbFacebook = function () {
  var _this = this;
  this.isConnected = false;
  this.permissions = '';
  this.pathRouteInSession = null;
  this.pathSecurityLogin = null;
  this.me = null;

  $(document).ready(function () {
    _this.init();
  });
};

EbFacebook.prototype._ = function (id) {
  return $(document.getElementById(id)).html();
};

EbFacebook.prototype.setPermissions = function (permissions) {
  this.permissions = permissions;

  return this;
};

EbFacebook.prototype.setMe = function (userData) {
  this.me = JSON.parse(userData);

  return this;
};

EbFacebook.prototype.init = function () {
  /* Init vars with DOM */
  this.pathRouteInSession = $('#pathRouteInSession').val();
  this.pathSecurityLogin = $('#pathSecurityLogin').val();
  this.isConnected = $('#isConnected').val() === 'yes' ? true : false;

  this.initFb();
  this.initUserForm();
  this.initPopin();
};

EbFacebook.prototype.addError = function (e, type, text) {
  var _this = this;

  if (typeof text === 'undefined') {
    text = type === 'checkbox' ? _this._('eb_facebook.check') : _this._('eb_facebook.required');
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
            _this.addError(e, type, _this._('eb_facebook.invalid'));
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

EbFacebook.prototype.saveTarget = function (data, next) {
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
    });

  } else if (typeof next === 'function') return next();
};

EbFacebook.prototype.login = function (targetData) {
  var _this = this;

  var gologin = function () {
    window.location = _this.pathSecurityLogin;
  };

  FB.login(
    function (res) {
      if (res.status) {
        if (targetData) {
          _this.saveTarget(targetData, function () {
            gologin();
          });
        } else gologin();
      }
    }, { scope: _this.permissions }
  );
};

EbFacebook.prototype.initFb = function () {
  var _this = this;

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
              window.alert(_this._('layout.invitation'));
            }
          }
        });
      }
    });
  });

  $('.fbLogin').click(function (e) {
    if (!_this.isConnected || $(this).data('force')) {
      e.preventDefault();
      var targetData = false;
      var route = $(this).data('route');
      var route_params = $(this).data('route_params');
      var direct_url = (!route && !route_params && $(this).attr('href') !== '#') ? $(this).attr('href') : false;

      if ((typeof $(this).data('default') === 'undefined' || $(this).data('default') === 'false') && (route || route_params || direct_url)) {
        targetData = {
          direct_url: direct_url,
          route: route,
          route_params: route_params
        };
      }

      _this.login(targetData);
    }
  });
};

EbFacebook.prototype.picture = function (facebookId, width, height) {
  facebookId = facebookId || '100007098372755';

  var url = '//graph.facebook.com/' + facebookId + '/picture';
  if (width || height) {
    url += '?';
    if (width) url += 'width=' + width;
    if (width && height) url += '&';
    if (height) url += 'height=' + height;
  }

  return url;
};

EbFacebook.prototype.myPicture = function (width, height) {
  return this.picture(this.me.facebookId, width, height);
};

if (typeof jQuery !== 'undefined') {
  var EFB = new EbFacebook();
}