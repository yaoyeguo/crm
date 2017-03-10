var widget,widgets = {},Packages;
(function(window){
  var baseUrl = 'widgets',
      Package = Backbone.Model.extend({}),
      PackageList = Backbone.Collection.extend({
          model:Package
      });
      AppView = Backbone.View.extend({
          el:$('#LQ'),
          initialize:function(){
              this.listenTo(Packages, 'add', this.addOne);
              this.listenTo(Packages, 'reset', this.addAll);
          },
          addOne:function(package){
              var _widget = package.toJSON().name;
              var p = new window[_widget].view({model:package});
              var el = p.el;
              if(window.widgetEdit){
                  $(el).addClass('Click-edit');
                  //if($(el).has('.isfixed')){
                  //    $(el).find('.isfixed').addClass('Click-edit');
                  //}
              }
              this.$el.append(p.el);
          },
          addAll:function(){
              this.$el.empty();
              Packages.each(this.addOne,this);
          }
      });
      Packages = new PackageList;
      App = new AppView;
  widget = function(Appdata,callback,disableEvent){
      if(typeof Appdata === 'string') {
          location.href = Appdata;
          return;
      }
      var i = 0, item;
      $('#LQ').empty();
      if (widgets.html_path){
          $.ajax({
              url:widgets.html_path,
              async:false,
              dataType:'text',
              success:function(re){
                  $(re).appendTo($(document.body));
              }
          });
          widgets.html_path = false;
      }
      var dataLength = Appdata.length;
      if(dataLength > 0){
          for(;i<dataLength;i++){
              item = Appdata[i];
              if(!document.getElementById(item.name+'_template')){
                  $.ajax({
                      url:baseUrl+'/'+item.name+'/template.html',
                      async:false,
                      dataType:'text',
                      success:function(re){
                        
                          $(re).appendTo($(document.body));
                      }
                  });
              }
              if(!window[item.name]){
                  $.ajax({
                      url:baseUrl+'/'+item.name+'/widget.js',
                      async:false,
                      dataType:'script'
                  });
                  $.ajax({
                      url:baseUrl+'/'+item.name+'/style.css',
                      async:false,
                      success:function(re){
                          $('<style>'+re+'</style>').appendTo($(document.head));
                      }
                  });
              }
              var _widget = window[item.name];
              if (!_widget) throw new Error('no widgets: ' + item.name);
              if (typeof(_widget.view)=="object") {
                if (disableEvent) {
                  _widget.view.events = {};
                }
                  _widget.view.template = _.template($('#'+_widget.name+'_template').html());
                  _widget.view = Backbone.View.extend(_widget.view);
              }
              if (!item.data) {
                item.data = {}
              }
              item.data = $.extend(true,{},window[item.name].defaults,item.data);
          }
          Packages.add(Appdata);
          for(i = 0;i<dataLength;i++){
              item = Appdata[i];
              callback && callback(item,{
                index:i,
                id: new Date().valueOf()
              });
          }
      }
  };
  widgets.config = function(obj){
      baseUrl = obj.baseUrl;
      $.extend(widgets,obj);
  }
})(window);
