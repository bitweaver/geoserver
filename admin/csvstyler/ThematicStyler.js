/**
 * ThematicStyler.js
 * Copyright (c) 2008 The Open Planning Project
 * Not yet licensed for distribution.
 */
var Styler={comboWin:null,showCombo:function(layers){if(!this.comboWin){var lf=new Styler.LayerStyleRulesForm(layers);this.comboWin=new Ext.Window({title:"Style Editor",layout:"fit",closeAction:"hide",width:300,height:160,plain:true,items:lf.form,buttons:[{text:"Edit",handler:function(){if(lf.selected.rule){Styler.showSymbolizers(lf.selected.rule,lf.selected.ruleIndex);}}}]});}
this.comboWin.show();return this.comboWin;},showLayers:function(layers){var lf=new Styler.LayersForm(layers);var win=new Ext.Window({title:"Styled Layers",layout:"fit",closeAction:"hide",width:300,height:120,plain:true,items:lf.form,buttons:[{text:"Edit Styles",handler:function(){var name=lf.form.getForm().getValues().name;}}]});win.show();return win;},showStyles:function(layer){var layerTitle=layer.title||layer.name;var styles=layer.userStyles;var sf=new Styler.StylesForm(styles);var win=new Ext.Window({title:"Style Editor ("+layerTitle+")",layout:"fit",closeAction:"hide",width:300,height:120,plain:true,items:sf.form,buttons:[{text:"Edit Rules",handler:function(){var index=sf.form.getForm().getValues().index;if(index!=""){Styler.showRules(styles[index]);}}}]});win.show();return win;},showRules:function(style){var styleTitle=style.title||style.name||"Unnamed Style";var rf=new Styler.RulesForm(style);var win=new Ext.Window({title:"Rule Editor ("+styleTitle+")",layout:"fit",closeAction:"hide",width:300,height:120,plain:true,items:rf.form,buttons:[{text:"Edit",handler:function(){var index=rf.form.getForm().getValues().rule;if(index!=""){Styler.showSymbolizers(style.rules[index],index);}}}]});win.show();return win;},colorPicker:null,cssColors:{aqua:"#00FFFF",black:"#000000",blue:"#0000FF",fuchsia:"#FF00FF",gray:"#808080",green:"#008000",lime:"#00FF00",maroon:"#800000",navy:"#000080",olive:"#808000",purple:"#800080",red:"#FF0000",silver:"#C0C0C0",teal:"#008080",white:"#FFFFFF",yellow:"#FFFF00"},getHexColor:function(color){var hex;if(color.match(/^#[0-9a-f]{6}$/i)){hex=color;}else{hex=Styler.cssColors[color.toLowerCase()]||null;}
return hex;},showPicker:function(field){var picker;if(!this.colorPicker){picker=new Ext.ux.ColorPanel({hidePanel:false,autoHeight:false});this.colorPicker=new Ext.Window({title:"Color Picker",layout:"fit",closeAction:"hide",width:405,height:300,plain:true,items:picker});}else{picker=this.colorPicker.items.items[0];}
picker.purgeListeners();var color=field.getValue();var hex=this.getHexColor(color);if(hex){picker.setColor(hex.substring(1));}
picker.on({pickcolor:function(picker,color){field.setValue("#"+color);}});this.colorPicker.show();return this.colorPicker;},colorField:function(field){var color=field.getValue();var hex=Styler.getHexColor(color);if(hex){var rgb=Ext.ux.ColorPicker.prototype.hexToRgb(hex.substring(1));var hsv=Ext.ux.ColorPicker.prototype.rgbToHsv(rgb[0],rgb[1],rgb[2]);field.getEl().setStyle({"background":hex,"color":(hsv[2]>0.6)?"black":"white"});}else{field.getEl().setStyle({"background":"#ffffff","color":"black"});}},onUpdate:function(){},symbolizers:{},showSymbolizers:function(rule,index){if(!this.symbolizers[rule.id]){var ruleTitle=rule.title||rule.name||("Rule "+index);var sf=new Styler.SymbolizersForm(rule);this.symbolizers[rule.id]=new Ext.Window({title:"Symbolizer ("+ruleTitle+")",layout:"fit",closeAction:"hide",width:320,height:535,autoScroll:true,plain:true,listeners:{beforehide:function(){sf.updateRule();Styler.onUpdate(rule);}},items:sf.form,buttons:[{text:"Apply",handler:function(){sf.updateRule();Styler.onUpdate(rule);}}]});}
this.symbolizers[rule.id].show();return this.symbolizers[rule.id];}};(function(){Ext.form.VTypes.numeric=function(value){return!isNaN(parseFloat(value));};var integer=/^[0-9]+$/;Ext.form.VTypes.integer=function(value){return integer.test(value);};Ext.form.VTypes.fraction=function(value){return!isNaN(value)&&value>=0&&value<=1;};})();var ThematicStyler={iframeId:"ThematicStylerIframe",DEFAULT_NAMESPACE_PREFIX:"topp",GeoServer:null,GeoServerWithoutProxy:null,layers:null,dirtyLayers:[],symbolizers:{},combos:{},showUpload:function(){var geomLayers=new Ext.data.JsonStore({url:ThematicStyler.GeoServer+"/rest/csv/geometryLayers",autoLoad:true,fields:["name","title"]});var uf=new ThematicStyler.UploadForm(geomLayers);this.uploadWin=new Ext.Window({title:"Upload Dataset To Create Layer",layout:"fit",closeAction:"hide",width:300,height:220,plain:true,items:uf.form,buttons:[{text:"Upload",handler:function(btn){uf.form.getForm().submit({waitTitle:"Upload in progress",waitMsg:"Sending data...",success:function(form,result){if(result.result.success=="true"){this.uploadWin.close();this.onUpload();this.showCombo(result.result.dataLayers);}else{alert(result.result.message);}},failure:function(form,result){if(result.failureType=="client"){alert("Required information is missing. Please check your form.");}
else{alert(result.response.responseText);}},scope:this});},scope:this}]});this.uploadWin.show();},showCombo:function(layers){if(!this.comboWin||this.layers!=layers){var title="Style Editor";if(layers){title+=" (layers modified by upload only)"}
var lf=new ThematicStyler.LayerStyleRulesForm(layers);this.comboWin=new Ext.Window({id:OpenLayers.Util.createUniqueID("ThematicStyler"),title:title,layout:"fit",closeAction:"hide",width:300,height:135,plain:true,items:lf.form,buttons:[{text:"Edit",disabled:true,handler:function(){ThematicStyler.showSymbolizers(lf.selected.layer,lf.selected.style);}}]});};this.comboWin.show();return this.comboWin;},onUpdate:function(rule){},onUpload:function(){},showSymbolizers:function(layer,style){if(!this.symbolizers[style]){var chooser=new ThematicStyler.SymbolizerChooser(layer,style);var styler=this;chooser.onCancel=function(){chooser.window.hide();}
chooser.onApply=function(){if(OpenLayers.Util.indexOf(this.dirtyLayers,layer)==-1){this.dirtyLayers.push(layer);}
this.onLayerChange(layer);chooser.window.hide();}.bind(this);this.symbolizers[style]=chooser.window;}
this.symbolizers[style].show();return this.symbolizers[style];},publish:function(){var layers=new Array(this.dirtyLayers.length);var urls=new Array(this.dirtyLayers.length);var layer,url;for(var i=0;i<this.dirtyLayers.length;i++){layer=this.dirtyLayers[i].name;if(layer.indexOf(":")==-1){layer=this.DEFAULT_NAMESPACE_PREFIX+":"+layer;}
layers[i]=layer;url=ThematicStyler.GeoServerWithoutProxy+"/gwc/service/gmaps?layers="+
layers[i]+"&zoom={Z}&x={X}&y={Y}";urls[i]='<a target="_blank" href="googletest.html?'+url+'">'+layer+'</a>';}
var layersPublished=function(request){this.dirtyLayers=[];ThematicStyler.onPublish();Ext.Msg.alert("Modified layers published","Click on the links below for a preview and the GMaps URL:<br/>"+urls.join("<br/>"));}
Ext.Ajax.request({url:ThematicStyler.GeoServer+"/gwc/truncate/?layers="+
layers.join("&layers="),method:"GET",success:layersPublished,failure:layersPublished,scope:this});},showSlds:function(){var urls=new Array(this.dirtyLayers.length);var layer,url;for(var i=0;i<this.dirtyLayers.length;i++){layer=this.dirtyLayers[i].name;url=ThematicStyler.GeoServerWithoutProxy+"/rest/styles/"+
layer+".xml";urls[i]='<a target="_blank" href="'+url+'">'+url+'</a>';}
Ext.Msg.alert("SLD URLs","The URLs for the style XMLs are<br/>"+urls.join("<br/>"));}};ThematicStyler.UniqueValueSymbolizer=new OpenLayers.Class({TITLE:"Unique Value",customControls:[],customColorsValid:false,initialize:function(layer,style){this.layer=layer;this.style=style;this.form=this.createForm();},createForm:function(){var controls=[];controls=controls.concat(this.createPersistentControls());controls=controls.concat(this.createCustomControls())
var form=new Ext.FormPanel({title:this.TITLE,layout:"form",border:true,bodyStyle:"padding: 5px",items:controls,labelWidth:50});return form;},createPersistentControls:function(){var symbolizer=this;var url=ThematicStyler.GeoServer+"/rest/sldservice/"+this.layer.name+"/attributes/";var intervalType=new Ext.form.Hidden({name:"classMethod",value:"unique"});var properties=new Ext.form.ComboBox({fieldLabel:"Property",hiddenName:"property",autoWidth:true,store:new Ext.data.JsonStore({url:url,fields:["name","type"],autoLoad:true}),valueField:"name",displayField:"name",mode:"local",triggerAction:"all",emptyText:"Select a field...",selectOnFocus:true,editable:false,listWidth:150});var colors=new Ext.form.ComboBox({fieldLabel:"Colors",hiddenName:"colorRamp",store:new Ext.data.SimpleStore({fields:['name','display'],data:[["random","Random"],["red","Red"],["blue","Blue"],["custom","Custom"]]}),displayField:'display',valueField:'name',emptyText:"Select colors...",mode:"local",triggerAction:"all",editable:false,listWidth:150,listeners:{select:function(field){var applyButton=this.form.ownerCt.ownerCt.buttons[0];if(field.getValue()=="custom"){symbolizer.showCustomControls();if(this.customColorsValid==false){applyButton.disable();}}else{symbolizer.hideCustomControls();applyButton.enable();}},scope:this}});return[properties,intervalType,colors];},createCustomControls:function(){var startTextField=new Ext.form.TextField({name:"startColor",hideLabel:true,allowBlank:false,listeners:{valid:Styler.colorField}});var midTextField=new Ext.form.TextField({name:"midColor",hideLabel:true,allowBlank:false,listeners:{valid:Styler.colorField}});var endTextField=new Ext.form.TextField({name:"endColor",hideLabel:true,allowBlank:false,listeners:{valid:Styler.colorField}});var startColor=new Ext.form.FieldSet({title:"Starting Color",autoHeight:true,items:[startTextField,{xtype:"button",text:"Color Picker",handler:function(){Styler.showPicker(startTextField);}}]});var midColor=new Ext.form.FieldSet({title:"Middle Color",autoHeight:true,items:[midTextField,{xtype:"button",text:"Color Picker",handler:function(){Styler.showPicker(midTextField);}}]});var endColor=new Ext.form.FieldSet({title:"Ending Color",autoHeight:true,items:[endTextField,{xtype:"button",text:"Color Picker",handler:function(){Styler.showPicker(endTextField);}}]});this.customControls=[startColor,midColor,endColor]
var enableDisableApplyButton=function(){var applyButton=this.form.ownerCt.ownerCt.buttons[0];if(startTextField.getValue()&&midTextField.getValue()&&endTextField.getValue()){applyButton.enable();this.customColorsValid=true;}else{applyButton.disable();this.customColorsValid=false;}};startTextField.on({valid:enableDisableApplyButton,scope:this});midTextField.on({valid:enableDisableApplyButton,scope:this});endTextField.on({valid:enableDisableApplyButton,scope:this});this.hideCustomControls();return this.customControls;},showCustomControls:function(){for(var index=0;index<this.customControls.length;index++){this.customControls[index].show();}},hideCustomControls:function(){for(var index=0;index<this.customControls.length;index++){this.customControls[index].hide();}},getForm:function(){return this.form;}});ThematicStyler.IntervalSymbolizer=new OpenLayers.Class(ThematicStyler.UniqueValueSymbolizer,{TITLE:"Interval",createPersistentControls:function(){var controls=ThematicStyler.UniqueValueSymbolizer.prototype.createPersistentControls.apply(this,arguments);controls[0].store.on({"load":function(){var r;while((r=this.find("type",/string|geom|multi/))!=-1){this.remove(this.getAt(r));}}});var intervalType=new Ext.form.ComboBox({fieldLabel:"Type",hiddenName:"classMethod",store:new Ext.data.SimpleStore({fields:['name','display'],data:[["equalInterval","Equal Interval"],["quantile","Quantile Interval"]]}),displayField:'display',valueField:'name',emptyText:"Select interval type...",mode:"local",triggerAction:"all",editable:false,listWidth:150});controls[1]=intervalType;var intervals=new Ext.form.ComboBox({fieldLabel:"Num. of Intervals",hiddenName:"classNum",store:new Ext.data.SimpleStore({fields:['name','display'],data:[["4","4 intervals"],["5","5 intervals"],["6","6 intervals"],["7","7 intervals"],["8","8 intervals"],["9","9 intervals"],["10","10 intervals"],["11","11 intervals"],["12","12 intervals"],["13","13 intervals"],["14","14 intervals"],["15","15 intervals"]]}),displayField:'display',valueField:'name',emptyText:"Select intervals...",mode:"local",triggerAction:"all",editable:false,listWidth:150});controls.splice(2,0,intervals);return controls;},getForm:function(){return this.form;}});ThematicStyler.SymbolizerChooser=new OpenLayers.Class({selectedTab:null,WINDOW_HEIGHT:500,TABS:[ThematicStyler.UniqueValueSymbolizer,ThematicStyler.IntervalSymbolizer],initialize:function(layer,style){this.layer=layer;this.style=style;this.createTabPanel();this.createWindow();},onApply:function(form,result){},onCancel:function(){},createWindow:function(){var win=this;this.window=new Ext.Window({title:"Edit Style ("+this.style+")",id:OpenLayers.Util.createUniqueID("ThematicStyler"),closeAction:"hide",width:300,height:this.WINDOW_HEIGHT,layout:"border",plain:true,items:[this.tabPanel],buttons:[{text:"Apply",disabled:true,handler:function(btn){var url=ThematicStyler.GeoServer+"/rest/sldservice/"+this.layer.name+"/styles/"+this.style;var form=this.tabPanel.getActiveTab().getForm();Ext.Ajax.request({url:url,method:"POST",form:form.id,success:function(form,result){this.onApply(form,result);},failure:function(form,result){alert("Error while applying style:\n"+form.responseText);},scope:this});},scope:this},{text:"Cancel",handler:function(btn){OpenLayers.Console.log("Handled.");this.onCancel();},scope:this}]});},createTabPanel:function(){var tabs=[]
var tab;for(var index=0;index<this.TABS.length;index++){tab=new this.TABS[index](this.layer,this.style);tabs.push(tab.getForm())}
this.tabPanel=new Ext.TabPanel({items:tabs,activeTab:0,region:"center",deferredRender:false});}});ThematicStyler.UploadForm=OpenLayers.Class({form:null,geomLayers:null,uploadField:null,layerField:null,joinField:null,initialize:function(geomLayers){this.geomLayers=geomLayers;this.createForm();},createForm:function(){ThematicStyler.UploadForm.formConfig.url=ThematicStyler.GeoServer+"/rest/csv/dataLayers";this.form=new Ext.form.FormPanel(ThematicStyler.UploadForm.formConfig);this.uploadField=this.form.items.items[0];this.layerField=this.form.items.items[1];this.joinField=this.form.items.items[2];this.layerField.store=this.geomLayers;this.layerField.on({select:function(combo,record,index){this.joinField.store.proxy.conn.url=ThematicStyler.GeoServer+"/rest/sldservice/"+this.layerField.value+"/attributes";this.joinField.store.load();this.joinField.reset();},scope:this});this.joinField.store.on({"load":function(){var r;while((r=this.find("type",/decimal|long|int|geom|multi/))!=-1){this.remove(this.getAt(r));}}});},updateFieldsStore:function(){}});ThematicStyler.UploadForm.uploadConfig={xtype:"textfield",fieldLabel:"Please select a csv file to upload",name:"file",inputType:"file",allowBlank:false};ThematicStyler.UploadForm.layerConfig={xtype:"combo",fieldLabel:"Geometry layer for the dataset",hiddenName:"geometryLayer",valueField:"name",displayField:"title",allowBlank:false,editable:false,mode:"local",triggerAction:"all",emptyText:"Please select a layer...",selectOnFocus:true,anchor:"100%"};ThematicStyler.UploadForm.fieldConfig={xtype:"combo",fieldLabel:"Join field",hiddenName:"joinField",store:new Ext.data.JsonStore({fields:["name","type"]}),valueField:"name",displayField:"name",allowBlank:false,editable:false,mode:"local",triggerAction:"all",emptyText:"Please select a join field...",selectOnFocus:true,anchor:"100%"}
ThematicStyler.UploadForm.formConfig={layout:"form",labelAlign:"top",defaultType:"textfield",fileUpload:true,onSubmit:Ext.emptyFn,bodyStyle:"padding: 5px 5px 0",width:350,items:[ThematicStyler.UploadForm.uploadConfig,ThematicStyler.UploadForm.layerConfig,ThematicStyler.UploadForm.fieldConfig]};Styler.LayersForm=new OpenLayers.Class({initialize:function(layers){this.layers=layers;this.createForm();},createForm:function(){var records=[];var layer;for(var name in this.layers){records[records.length]={name:name,title:this.layers[name].title||name};}
this.form=new Ext.form.FormPanel(Styler.LayersForm.formConfig.formConfig);this.form.items.items[0].store.loadData({layers:records});},CLASS_NAME:"Styler.LayersForm"});Styler.LayersForm.fieldConfig={xtype:"combo",fieldLabel:OpenLayers.i18n("Layers"),hiddenName:"name",store:new Ext.data.JsonStore({data:{layers:[]},root:"layers",fields:["name","title"]}),valueField:"name",displayField:"title",typeAhead:true,mode:"local",triggerAction:"all",emptyText:OpenLayers.i18n("Select a layer..."),selectOnFocus:true,anchor:"95%"};Styler.LayersForm.formConfig={layout:"form",labelWidth:50,bodyStyle:"padding: 10px",defaultType:"textfield",onSubmit:Ext.emptyFn,bodyStyle:"padding: 5px 5px 0",width:350,defaults:{width:230},items:[Styler.LayersForm.fieldConfig]};Styler.StylesForm=new OpenLayers.Class({initialize:function(styles){this.styles=styles;this.createForm();},createForm:function(){var records=new Array(this.styles.length);var style;for(var i=0;i<this.styles.length;++i){var style=this.styles[i];records[i]={index:i,title:style.title||style.name||(OpenLayers.i18n("Style")+" "+i)};}
this.form=new Ext.form.FormPanel(Styler.StylesForm.formConfig);this.form.items.items[0].store.loadData({styles:records});},CLASS_NAME:"Styler.StylesForm"});Styler.StylesForm.fieldConfig={xtype:"combo",fieldLabel:OpenLayers.i18n("Styles"),hiddenName:"index",store:new Ext.data.JsonStore({data:{styles:[]},root:"styles",fields:["index","title"]}),valueField:"index",displayField:"title",typeAhead:true,mode:"local",triggerAction:"all",emptyText:OpenLayers.i18n("Select a style..."),selectOnFocus:true,anchor:"95%"};Styler.StylesForm.formConfig={layout:"form",labelWidth:50,bodyStyle:"padding: 10px",defaultType:"textfield",onSubmit:Ext.emptyFn,bodyStyle:"padding: 5px 5px 0",width:350,defaults:{width:230},items:[Styler.StylesForm.fieldConfig]};ThematicStyler.LayerStyleRulesForm=new OpenLayers.Class({selected:null,layers:null,selected:null,initialize:function(layers){this.selected={};this.layers=layers;this.createForm();},createForm:function(){var layersFieldConfig=Styler.LayersForm.fieldConfig;var stylesFieldConfig=Styler.StylesForm.fieldConfig;stylesFieldConfig.store=new Ext.data.JsonStore({fields:["name","url"]});stylesFieldConfig.valueField="name";stylesFieldConfig.displayField="name";stylesFieldConfig.editable=false
stylesFieldConfig.store.on({load:function(store){if(store.getTotalCount()==1){var item=store.getAt(0);this.stylesField.setValue(item.data.name);this.stylesField.fireEvent("select",this.stylesField,item,0);}},scope:this});this.form=new Ext.form.FormPanel(ThematicStyler.LayerStyleRulesForm.formConfig);this.layersField=this.form.items.items[0];this.stylesField=this.form.items.items[1];if(this.layers){var records=[];var layer,name;for(var i=0;i<this.layers.length;++i){layer=this.layers[i];name=layer.name;records[records.length]={name:name,title:layer.title||layer.name};}
this.layersField.store.loadData({layers:records});}else{var store=new Ext.data.JsonStore({url:ThematicStyler.GeoServer+"/rest/csv/dataLayers",fields:["title","name"]});this.layersField.store=store;this.layersField.store.load();}
this.layersField.on({select:function(combo,record,index){this.selected.layer=record.data;this.selected.style=null;this.updateStylesStore();ThematicStyler.onLayerSelect(this.selected.layer);},scope:this});this.stylesField.on({select:function(combo,record,index){var win=this.form.ownerCt;this.selected.style=record.data.name;win.buttons[0].enable();},scope:this});},updateStylesStore:function(){var store=this.stylesField.store;store.proxy.conn.url=ThematicStyler.GeoServer+"/rest/sldservice/"+this.selected.layer.name+"/styles";store.load();this.stylesField.reset();},CLASS_NAME:"ThematicStyler.LayerStyleRulesForm"});ThematicStyler.LayerStyleRulesForm.formConfig={layout:"form",labelWidth:50,bodyStyle:"padding: 10px",defaultType:"textfield",onSubmit:Ext.emptyFn,bodyStyle:"padding: 5px 5px 0",width:350,defaults:{width:230},items:[Styler.LayersForm.fieldConfig,Styler.StylesForm.fieldConfig]};