Ext.namespace('Exj','Exj.Global','Exj.Panel','Exj.pg','Exj.ui','Exj.ui.modules','Exj.ui.helpers','Exj.Const','Exj.action.grid','Exj.files','Exj.URI','Exj.browser','Exj.mail');Exj.verApp='1.3.0';Exj.version=Exj.verApp;Exj.modulesApp=new Array();Exj._langCurrent='';Exj.isModeDebug=!0;Exj.Const._EXJ_ESTADO_OK=0;Exj.Const._EXJ_ESTADO_ERROR=1;Exj.Const._EXJ_MSG_TIPO_INFO=1;Exj.Const._EXJ_MSG_TIPO_ERROR=2;Exj.Const._EXJ_MSG_TIPO_WARNING=3;Exj.Const._EXJ_MSG_TIPO_NOTIFY=4;Exj.Const._EXJ_MSG_TIPO_HTML=6;Exj.eval=function(codeJS,valueDefault,objResponse){if(!codeJS){return valueDefault}
if(objResponse&&Ext.isObject(objResponse)){objResponse.codeJS=codeJS;objResponse.msgError=''}
var value='';try{value=eval(codeJS)}catch(ex){value=valueDefault;var msgError='';if(ex&&ex.message){msgError=ex.message}else{msgError=ex}
if(!msgError){msgError='UNKNOWN ERROR, evaluating:<br/>'+codeJS}
if(objResponse&&Ext.isObject(objResponse)){objResponse.msgError=msgError;if(objResponse.showMsgError){Exj.moe(msgError,'EVALUATING ERROR JS')}}else{Exj.moe(msgError,'EVALUATING ERROR JS')}}
return value};Exj.isValidJSON=function(str){if(!str){return!1}
var isValidJSON=!1,objDecode=null;try{eval('objDecode='+str+';');if(Ext.isObject(objDecode)||Ext.isArray(objDecode)){isValidJSON=!0}}catch(ex){}
return isValidJSON};Exj._setViewportMain=function(refVPMain){Exj._refVPMain=refVPMain};Exj.appMainReloadStore=function(scopeCalled){var tpMain=Exj.__tpMain;if(!tpMain||!tpMain.activeTab){return!1}
if(!tpMain.activeTab.isVUModuleMain){return!1}
var grids=tpMain.activeTab.findByType(Ext.grid.GridPanel);if(!grids.length){return!1}
for(var i=0,grid;i<grids.length;i++){grid=grids[i];if(!grid.store||grid.store.isDestroyed||grid.hidden||grid.isModeLocal||!grid.store.proxy){continue}
grid.store.reload()}
return!0};Exj.getHeightApp=function(){if(!Exj._refVPMain){return 600}
return Exj._refVPMain.getHeight()};Exj.getWidthApp=function(){if(!Exj._refVPMain){return 800}
return Exj._refVPMain.getWidth()};Exj.setFieldText=function(compField,fieldLabel,labelSeparator){if(!compField.label||!compField.label.dom){return!1}
if(labelSeparator===undefined){labelSeparator=':'}
if(fieldLabel&&labelSeparator){fieldLabel=fieldLabel+labelSeparator}
compField.label.dom.innerHTML=fieldLabel;return!0};Exj.DocPanel=Ext.extend(Ext.Panel,{closable:!0,autoScroll:!0,initComponent:function(){if(this.optsBar&&this.optsBar.length){var _tbar=['->'];for(var i=0,optBar;i<this.optsBar.length;i++){optBar=this.optsBar[i];_tbar.push({text:optBar.text,handler:this.scrollToMember.createDelegate(this,[optBar.member]),iconCls:optBar.iconCls});if(i<this.optsBar.length-1){_tbar.push('-')}}
Ext.apply(this,{tbar:_tbar})}
Exj.DocPanel.superclass.initComponent.call(this)},scrollToMember:function(member){var el=Ext.fly(member);if(el){var top=(el.getOffsetsTo(this.body)[1])+this.body.dom.scrollTop;this.body.scrollTo('top',top-25,{duration:0.75,callback:this.hlMember.createDelegate(this,[member])})}},scrollToSection:function(id){var el=Ext.getDom(id);if(el){var top=(Ext.fly(el).getOffsetsTo(this.body)[1])+this.body.dom.scrollTop;this.body.scrollTo('top',top-25,{duration:0.5,callback:function(){Ext.fly(el).next('h2').pause(0.2).highlight('#8DB2E3',{attr:'color'})}})}},hlMember:function(member){var el=Ext.fly(member);if(el){if(tr=el.up('tr')){tr.highlight('#cadaf9')}}}});Exj.showHelp=function(params){params=Ext.apply({url:'',titleModule:'Ayuda',iconCls:''},params);if(!params.url){Exj.moe('No se indicó la url','ERROR - AYUDA');return}
Exj.submitAction({method:'GET',mask:'Por favor espere...',url:params.url,withParams:!1,fnSuccess:function(response){if(!response.data){Exj.moi('Ayuda no disponible',params.titleModule);return}
var items=response.data.items,panels=new Array();if(!items||!items.length){Exj.moe('No se ha retornado estructura requerida, para mostrar ayuda!',params.titleModule);return}
var optsBar,idRef;for(var i=0,item;i<items.length;i++){item=items[i];optsBar=new Array();idRef='AppHlpIni'+i;optsBar.push({text:'Home',member:idRef,iconCls:'app-btn-home'});var contentPanelHTML='<a id="'+idRef+'"></a>';contentPanelHTML+='<div>'+item.description+'</div>';if(item.arts&&item.arts.length){contentPanelHTML+='<br/>';contentPanelHTML+='<div>';var cntArtsHTML='<table>';for(var j=0,dataArt;j<item.arts.length;j++){dataArt=item.arts[j];cntArtsHTML+='<tr><td class="exj-hlp-content-main-td">';idRef='AppHlpArt'+(i+j);optsBar.push({text:dataArt.title,member:idRef,iconCls:dataArt.iconCls});cntArtsHTML+='<div class="contentpaneopen">';cntArtsHTML+='<a id="'+idRef+'"></a>';cntArtsHTML+='<h2 class="contentheading">'+dataArt.title+'</h2>';cntArtsHTML+='<div class="article-content">'+dataArt.introtext+'</div>';cntArtsHTML+='</div>';cntArtsHTML+='</td></tr>'}
cntArtsHTML+='</table>';contentPanelHTML+='<div>'+cntArtsHTML+'</div>';contentPanelHTML+='</div>'}
panels.push(new Exj.DocPanel({title:item.title,html:contentPanelHTML,cls:(item.cls?item.cls:'exj-hlp-content-main'),height:Exj.calcHeight(72),autoWidth:!0,optsBar:optsBar}))}
var pnlCntWin;if(panels.length>1){pnlCntWin={xtype:'panel',split:!0,autoWidth:!0,layout:'accordion',height:Exj.calcHeight(90),items:panels}}else{pnlCntWin=panels[0];pnlCntWin.split=!0;pnlCntWin.height=Exj.calcHeight(90)}
var win=Exj.newWindow({title:(response.data.title?response.data.title:params.titleModule+' - Help'),iconCls:params.iconCls,autoHeight:!0,maximizable:!0,minHeight:Exj.calcHeight(33),minWidth:Exj.calcWidth(33),width:Exj.calcWidth(96),items:[pnlCntWin],listeners:{show:function(w){w.maximize()}}});win.show()}})};Exj.convertDataTableToHTML=function(params){params=Ext.apply({dataTable:null,classTable:''},params);var dataTable=params.dataTable;var classTable=params.classTable;if(!dataTable){return'ERROR. No se definió prop dataTable!'}
if(!Ext.isObject(dataTable)){return dataTable}
var isPercentWidths=dataTable.isPercentWidths;var widthsCols=dataTable.widthsCols;var propsTable=new Array();if(classTable){propsTable.push('class="'+classTable+'"')}
if(dataTable.width){propsTable.push('width='+dataTable.width)}
if(dataTable.border!==undefined&&dataTable.border!==null){propsTable.push('border='+(dataTable.border?"true":"false"))}
if(dataTable.align){propsTable.push('align='+dataTable.align)}
propsTable=propsTable.join(' ');html=new Array();html.push('<table '+propsTable+'>');if(widthsCols&&widthsCols.length>0&&dataTable.columns){html.push('<tr style="display: none;">');for(var indexCol=0,widthCol,attrTD;indexCol<dataTable.columns;indexCol++){widthCol=widthsCols[indexCol];attrTD='';if(widthCol&&widthCol>0){attrTD+=' width='+widthCol;if(isPercentWidths){attrTD+='%'}else{attrTD+='px'}}
html.push('<td '+attrTD+'></td>')}
html.push('</tr>')}
var propsCol,valueItem,styleCol;for(var indexRow=0,row=null;indexRow<dataTable.rows.length;indexRow++){row=dataTable.rows[indexRow];html.push('<tr>');for(var indexCol=0,col=null;indexCol<row.cols.length;indexCol++){col=row.cols[indexCol];valueItem=col.value;propsCol=new Array();styleCol=col.style;if(!styleCol){styleCol=new Array()}
if(styleCol&&!Ext.isArray(styleCol)){styleCol=styleCol.split(';')}
if(col.fontSize){styleCol.push('font-size: '+col.fontSize+(col.unitSize?col.unitSize:'px'))}
if(col.color){styleCol.push('color: '+col.color)}
if(styleCol&&styleCol.length>0){propsCol.push('style="'+styleCol.join(';')+'"')}
if(col.colspan){propsCol.push('colspan='+col.colspan)}
if(col.rowspan){propsCol.push('rowspan='+col.rowspan)}
if(col.align){propsCol.push('align='+col.align)}
propsCol=propsCol.join(' ');if(propsCol){html.push('<td '+propsCol+'>')}else{html.push('<td>')}
if(col.value){if(Ext.isObject(valueItem)){if(valueItem.isImage){if(!valueItem.unitSize){valueItem.unitSize='px'}
var imgHTML='<img';imgHTML+=' src="'+valueItem.src+'"';if(valueItem.height){imgHTML+=' height='+valueItem.height+valueItem.unitSize}
if(valueItem.width){imgHTML+=' height='+valueItem.width+valueItem.unitSize}
if(valueItem.alt){imgHTML+=' alt="'+valueItem.alt+'"'}
imgHTML+='/>';valueItem=imgHTML}}
if(col.isfontBold&&valueItem!=''){html.push('<b>'+valueItem+'</b>')}else{html.push(valueItem)}}
html.push('</td>')}
html.push('</tr>')}
html.push('</table>');html=html.join(' ');return html};Exj.newObjPrintShowIframe=function(configIframe){configIframe=Ext.apply({height:'300px',style:'border:0;background-color: white;padding: 3px 0 0 3px;',allowChangeNumSecFac:!1},configIframe);return Exj.newObjComponentIframe(configIframe)};Exj.newObjPrintHiddenIframe=function(configIframe){configIframe=Ext.apply({height:'90px',style:'border:0; display: none;'},configIframe);return Exj.newObjComponentIframe(configIframe)};Exj.newObjComponentIframe=function(configIframe){configIframe=Ext.apply({src:'',width:"100%",style:'border:0;',allowChangeNumSecFac:!1},configIframe);configIframe.tag='iframe';var idCmp=null;if(configIframe.id){idCmp=configIframe.id;delete configIframe.id}
var nameTmpl=null;if(configIframe.nameTmpl){nameTmpl=configIframe.nameTmpl;delete configIframe.nameTmpl}
var allowChangeNumSecFac=null;if(configIframe.allowChangeNumSecFac!==undefined){allowChangeNumSecFac=configIframe.allowChangeNumSecFac;delete configIframe.allowChangeNumSecFac}
objCmpIframe={xtype:'component',autoEl:configIframe,height:'auto',width:'auto',allowChangeNumSecFac:allowChangeNumSecFac};if(idCmp){objCmpIframe.id=idCmp}
if(nameTmpl){objCmpIframe.nameTmpl=nameTmpl}
return objCmpIframe};Exj.printReportIframeShow=function(paramsPrint){paramsPrint=Ext.apply({scope:null,idIframe:'',msgNoLoadReport:'No se a cargado el reporte!',paramsPrint:null},paramsPrint);if(!paramsPrint.idIframe){Exj.moe('No se indicó ID del frame para imprimir');return!1}
var cmpIframeShow=null;if(paramsPrint.scope){var cpms=paramsPrint.scope.find('id',paramsPrint.idIframe);if(cpms&&cpms.length>0){cmpIframeShow=cpms[0]}}else{cmpIframeShow=Ext.getCmp(paramsPrint.idIframe)}
if(!cmpIframeShow){Exj.moe('No se encontró ID Iframe: '+paramsPrint.idIframe);return!1}
if(!cmpIframeShow.el.dom.contentWindow||!cmpIframeShow.el.dom.contentWindow.imprimirReporte){if(paramsPrint.msgNoLoadReport){Exj.moi(paramsPrint.msgNoLoadReport)}
return!1}
cmpIframeShow.el.dom.contentWindow.imprimirReporte(paramsPrint.paramsPrint);return!0};Exj.getValuesReportIframe=function(params){var refWinRep=Exj.getContentWindowReportIframe(params);if(!refWinRep){return refWinRep}
return refWinRep.getValuesReport()};Exj.setValueReportIframe=function(params){if(!params.paramSetValue){return!1}
var refWinRep=Exj.getContentWindowReportIframe(params);if(!refWinRep){return refWinRep}
refWinRep.setValueCmpRep(params.paramSetValue.field,params.paramSetValue.value);return refWinRep};Exj.getContentWindowReportIframe=function(paramsPrint){paramsPrint=Ext.apply({scope:null,idIframe:'',msgNoLoadReport:'No se a cargado el reporte!'},paramsPrint);if(!paramsPrint.idIframe){Exj.moe('No se indicó ID del frame para obtener valores del reporte');return!1}
var cmpIframeShow=null;if(paramsPrint.scope){var cpms=paramsPrint.scope.find('id',paramsPrint.idIframe);if(cpms&&cpms.length>0){cmpIframeShow=cpms[0]}}else{cmpIframeShow=Ext.getCmp(paramsPrint.idIframe)}
if(!cmpIframeShow){Exj.moe('No se encontró ID Iframe: '+paramsPrint.idIframe);return!1}
if(!cmpIframeShow.el.dom.contentWindow||!cmpIframeShow.el.dom.contentWindow.getValuesReport){if(paramsPrint.msgNoLoadReport){Exj.moi(paramsPrint.msgNoLoadReport)}
return!1}
return cmpIframeShow.el.dom.contentWindow};Exj.loadReportIframeToPrinter=function(params){params=Ext.apply({urlReport:{}},params);params.urlReport.isPreView=0;params.urlReport.outScreen=0;params.urlReport.outPrint=1;return Exj.loadReportIframe(params)};Exj.loadReportIframeToScreen=function(params){params=Ext.apply({urlReport:{}},params);params.urlReport.outScreen=1;params.urlReport.outPrint=0;return Exj.loadReportIframe(params)};Exj.loadReportIframe=function(params){if(!params||!params.urlReport){return!1}
if(!params.id){Exj.moe('No se indicó id','ERROR Exj.loadReportIframeToScreen()');return!1}
var cmpRep=Ext.getCmp(params.id);if(!cmpRep){Exj.moe('No se encontró el componente id: '+params.id,'ERROR Exj.loadReportIframeToScreen()');return!1}
if(!params.urlReport.nameTmpl){params.urlReport.nameTmpl=cmpRep.nameTmpl;if(!params.urlReport.nameTmpl){Exj.moe('No se encontró indicó nombre del template del reporte.<br/>id: '+params.id,'ERROR Exj.loadReportIframeToScreen()');return!1}}
var iframeRep=cmpRep.getContentTarget().dom;if(params.urlReport.isPreView==undefined&&params.urlReport.outScreen){params.urlReport.isPreView=1}
if(params.urlReport.clearURL){iframeRep.src='';return!0}
if(params.allowChangeNumSecFac===undefined){params.allowChangeNumSecFac=cmpRep.allowChangeNumSecFac}
iframeRep.onload=function(pLoad1){if(params.onload){params.onload(pLoad1)}
if(params.urlReport.outScreen){if(pLoad1.currentTarget&&pLoad1.currentTarget.contentWindow&&params.allowChangeNumSecFac){pLoad1.currentTarget.contentWindow.AppBaseReport.setActionCambioNumFac(function(paramsFromReport){Exj.ui.helpers.FacturasUtil.showCambioNumFac({paramsFromReport:paramsFromReport})})}}};iframeRep.src=Exj.getURLReportHTML(params.urlReport);return!0};Exj.getHeadPrintHTML=function(senderBtn,nodeToPrint){return'<head><link href="./templates/sy_gym/css/impresion.css" type="text/css" rel="stylesheet"/></head>'};Exj.exjPrintNodeHTML=function(senderBtn,nodeToPrint){if(!nodeToPrint){nodeToPrint=senderBtn.parentNode.children[0]}
var tmp;tmp=window.open("","Impresión");tmp.document.open();tmp.document.write(Exj.getHeadPrintHTML());tmp.document.write(nodeToPrint.innerHTML);tmp.document.close();tmp.print();tmp.close()};Exj.exjPrintAllNodesHTML=function(senderBtn,pathSelector,scopeRoot){var c,tmp;if(scopeRoot&&scopeRoot.getEl){scopeRoot=scopeRoot.getEl().dom}
if(!pathSelector){pathSelector='.vu-page-print'}
var nodes=Ext.query(pathSelector,scopeRoot);if(!nodes||nodes.length==0){return}
tmp=window.open("","Impresión");tmp.document.open();tmp.document.write(Exj.getHeadPrintHTML());for(var i=0,nodeHTML;i<nodes.length;i++){nodeHTML=nodes[i];tmp.document.write('<div style="height:100%;page-break-after: always">'+nodeHTML.innerHTML);tmp.document.write('</div>')}
tmp.document.close();tmp.print();tmp.close()};Exj.applyActionPressEnter=function(formPanel,fnAction,xtypes){if(!fnAction){Exj.moe('No se ha definido a fn para aplicar accion Enter','ERROR DE IMPLEMENTACION');return!1}
var bf=formPanel.getForm();var foundField=!1;bf.items.each(function(f){if(!(f instanceof Ext.form.TextField)){return!0}
if(!f.enableKeyEvents){return!0}
if(xtypes){foundField=!1;for(var i=0,xtype;i<xtypes.length;i++){xtype=xtypes[i];if(f.xtype==xtype){foundField=!0}}
if(!foundField){return!0}}
f.addListener('keydown',function(txf,e){if(e.getKey()==e.ENTER){fnAction(txf)}})})};Exj.getParamFromGrid=function(grid,nameParam,valueDefault){valueDefault=(valueDefault===undefined?null:valueDefault);if(!grid||!grid.getStore){return valueDefault}
if(!grid.getStore().baseParams){return valueDefault}
var bp=grid.getStore().baseParams,objParams=null,valueParam=valueDefault;if(bp.criteria){if(Ext.isString(bp.criteria)){objParams=Ext.decode(bp.criteria)}else{objParams=bp.criteria}}else{objParams=bp}
if(!objParams||!Ext.isObject(objParams)){return valueDefault}
valueParam=objParams[nameParam];if(valueParam===undefined){valueDefault=valueDefault}
return valueParam};Exj.fixGridEditableRenderCombo=function(cfg){if(!cfg||!Ext.isObject(cfg)){return}
cfg=Ext.apply({grid:null,nameCol:'',nameFieldValue:'value',nameFieldText:'text',nameColId:'',fnChangeRecord:'',valueEmptyToId:null,valueNewToId:-1},cfg);var grid=cfg.grid;var nameCol=cfg.nameCol;var nameFieldValue=cfg.nameFieldValue;var nameFieldText=cfg.nameFieldText;if(!grid){Exj.moe('No se definió el grid en: Exj.fixGridEditableRenderCombo');return}
if(!nameCol){Exj.moe('No se definió el Nombre de Columna en: Exj.fixGridEditableRenderCombo');return}
if(!nameFieldText){nameFieldText=nameCol}
grid.addListener('afteredit',function(e){if(e.field==nameCol){var cellEditor=grid.getColumnModel().getCellEditor(e.column,e.row);var rEditor=null;if(e.value){var indexValue=cellEditor.field.store.findExact(cfg.nameFieldValue,e.value);if(indexValue<0){if(cellEditor.field.editable&&!cellEditor.field.forceSelection){e.record.set(cfg.nameColId,cfg.valueNewToId);return}
Exj.mou('ERROR RENDER COMBO.<br/>No se encontró valor: '+e.value);return}
rEditor=cellEditor.field.store.getAt(indexValue);if(!rEditor){Exj.mou('ERROR RENDER COMBO.<br/>No se encontró Registro con indice: '+indexValue);return}
e.record.set(nameCol,rEditor.get(nameFieldText));if(cfg.nameColId){e.record.set(cfg.nameColId,rEditor.get(cfg.nameFieldValue))}}else if(cfg.nameColId){if(cellEditor.field.allowBlank){e.record.set(cfg.nameColId,cfg.valueEmptyToId)}else{Exj.moi('El valor es requerido para: '+cellEditor.field.fieldLabel);e.record.set(nameCol,cellEditor.startValue)}}
if(cfg.fnChangeRecord){cfg.fnChangeRecord(e,rEditor)}}})};Exj.ui.modules.ExitApp=function(senderMenu,pGen){Ext.Msg.show({title:Exj.TITLE,msg:Exj.Idioma('Está seguro de salir de la aplicación')+'?',buttons:Ext.Msg.YESNO,fn:function(btn){if(btn=='no'){return}
Ext.get('form-login').dom.Submit.click()},animEl:senderMenu.getEl(),icon:Ext.MessageBox.QUESTION});return!1};Exj.createTemplateImage=function(){var tpl=new Ext.XTemplate('<tpl for=".">','<div class="thumb-wrap" id="{id_link}">','<a target="_blank" href="{url_link}">','<div class="thumb"><img style="height: 85px; width: 169px" src="{src_ui}" title="{url_link}"></div>','</a>','</div>','</tpl>','<div class="x-clear"></div>');return tpl};Exj.catchLoadTreePanelLoader=function(objTree,fnLoadSuccess){if(!objTree.loader){return!1}
objTree.loader.addListener('load',function(treeLoader,node,response){var responseObj=Ext.decode(response.responseText);if(!Exj.isSuccessResponse(responseObj)){return!1}
var childsNodes=responseObj.data;if(!childsNodes){childsNodes=responseObj.DataTopics.topics}
if(!childsNodes){return}
node.attributes.children=childsNodes;treeLoader.doPreload(node);if(fnLoadSuccess){fnLoadSuccess(childsNodes)}});return!0};Exj.round=function(num,decimales,fixed){if(decimales==undefined){decimales=2}
if(fixed===undefined){fixed=!1}
num=parseFloat(num);num=num.toFixed(decimales);if(fixed){return num}
return parseFloat(num)};Exj.calcWidthMin=function(percent,minWidth){if(!minWidth){minWidth=120}
var w=Exj.calcWidth(percent);if(w<minWidth){w=minWidth}
return w};Exj.calcWidth=function(percent,fullWidth){if(percent===undefined){percent=100}
if(!fullWidth){fullWidth=window.innerWidth}
if(percent<1){percent*=100}
var _w=fullWidth;if(!_w){_w=Exj.getWidthApp()}
return Exj.round((_w*(percent/100)),3)};Exj.rendererRound=function(num,decimales){return Exj.round(num,decimales,!0)};Exj.calcHeight=function(percent,fullHeight){if(percent===undefined){percent=100}
if(!fullHeight){fullHeight=window.innerHeight}
if(percent<1){percent*=100}
var _h=fullHeight;if(!_h){_h=Exj.getHeightApp()}
return Exj.round((_h*(percent/100)),3)};Exj.getSizeLayout=function(paramContainer){var layout;if(!paramContainer.container){if(paramContainer.layout){layout=paramContainer.layout}}else{layout=paramContainer;if(layout.container&&!layout.container.getLayoutTarget){layout=paramContainer.layout}}
var sizeLayout=null;if(layout&&layout.container){var target=layout.container.getLayoutTarget();if(target){sizeLayout=target.getViewSize();if(Ext.isIE&&Ext.isStrict&&sizeLayout.height==0){sizeLayout=target.getStyleSize()}
sizeLayout.width-=target.getPadding('lr');sizeLayout.height-=target.getPadding('tb')}}
if(!sizeLayout&&paramContainer.getSize){sizeLayout=paramContainer.getSize()}
return sizeLayout};Exj.LIMIT=15;Exj.LIMIT_MAX=60;Exj.TITLE='GYM Cloud';Exj.listWidth=Exj.calcWidth(30);Exj.FormatDate='d/m/Y';Exj.FormatDateTime='d/m/Y H:i';Exj.dateFormat='Y-m-d';Exj.dateTimeFormat='Y-m-d H:i:s';Exj.Panel.bodyStyle=Ext.isIE?'padding:0 0 2px 3px;':'padding: 1px 1px 1px 1px;';Exj.Panel.style=Ext.isIE?'padding:0 0 2px 3px;':'padding: 3px 3px 0px 0px;';Exj.getDom=function(idDom){var prefixPage='';return Ext.getDom(prefixPage+idDom)};Exj.getValueDom=function(idDom){var nodeDom=Exj.getDom(idDom);if(!nodeDom){return''}
return nodeDom.value};Exj.setterInnerHTML=function(idHTML,value){if(!idHTML){return!1}
var nodex=Ext.get(idHTML);if(!nodex||!nodex.dom){return!1}
nodex.dom.innerHTML=value};Exj.setterBaseParamsToStore=function(params){if(!params||!params.store){return!1}
var bpToSetters=new Array(),cmpRequired=null;if(params.components&&Ext.isArray(params.components)){for(var i=0,cmp,v;i<params.components.length;i++){cmp=params.components[i];if(!cmp.getValue){continue}
v=cmp.getValue();if(cmp instanceof Ext.form.ComboBox){if(!v){v=0}}
if(!v&&!cmp.allowBlank){cmpRequired=cmp;break}
bpToSetters.push({nameParam:cmp.name,value:v})}
if(cmpRequired){Exj.moi(cmpRequired.blankText,function(){cmpRequired.focus(!1,30)});return!1}}
if(params.params&&Ext.isObject(params.params)){for(params.params in field){bpToSetters.push({nameParam:p,value:params.params[field]})}}
if(bpToSetters.length==0){return!1}
var nSetters=0;for(var i=0,bpToSetter,nameParam,val;i<bpToSetters.length;i++){bpToSetter=bpToSetters[i];nameParam=bpToSetter.nameParam;val=bpToSetter.value;if(!params.store.baseParams||params.store.baseParams[nameParam]!=val){params.store.setBaseParam(nameParam,val);nSetters+=1}}
return(nSetters>0)};Exj.getUsuario=function(){var usrName=Exj.getValueDom('hUsrName');var usrType=Exj.getValueDom('hUsrType');var usr=new Object();usr.NOMBRE_USUARIO=usrName;usr.GRUPO_USUARIO=usrType;return usr};Exj.getGlobales=function(){var obj='';var jsonUsr=Exj.getValueDom('hfGlobales');if(!jsonUsr){return obj}
obj=Ext.util.JSON.decode(jsonUsr);return obj};Exj.browser={navCurrent:null,navVersion:null,navVerMin:''};Exj.browser.getGridNavs=function(){function renderDataVers(v,record){return''};var storeNav=new Ext.data.JsonStore({fields:['name','urlDownload','iconCls','desc','js',{name:'versions',convert:renderDataVers,defaultValue:[]},{name:'isSupported',type:'bool'},{name:'isRecommend',type:'bool'},{name:'canUpload',type:'bool'},{name:'canDownload',type:'bool'}]});var gridNav=new Ext.grid.GridPanel({store:storeNav,disableSelection:!0,emptyText:'No browsers to present',height:Exj.calcHeight(45),stripeRows:!0,autoExpandColumn:'colDesc',viewConfig:{forceFit:!0},columns:[{header:'Name',width:54,dataIndex:'name',renderer:function(value,metaData,r){var valueHTML='';if(r.data.iconCls){valueHTML=String.format('<div class="{0}" style="color:blue;padding:9px 33px;white-space:normal;"><b>{1}</b></div>',r.data.iconCls,value)}else{valueHTML=Exj.rendererTextHighlight(value)}
if(!r.data.urlDownload){return valueHTML}
return String.format('<a href="{0}" target="_blank">{1}</a>',r.data.urlDownload,valueHTML)}},{header:'Description',id:'colDesc',dataIndex:'desc',renderer:Exj.rendererText},{header:'Supported',width:21,sortable:!0,renderer:Exj.rendererTextSiNo,dataIndex:'isSupported'},{header:'Load',tooltip:'File Uploads',width:21,sortable:!0,renderer:Exj.rendererTextSiNo,dataIndex:'canUpload'},{header:'Download',tooltip:'Download Files',width:21,sortable:!0,renderer:Exj.rendererTextSiNo,dataIndex:'canDownload'},{header:'Recommended',width:30,sortable:!0,renderer:Exj.rendererTextSiNo,dataIndex:'isRecommend'}]});gridNav.loadDataNavs=function(){gridNav.store.loadData(Exj.Global.dataBrowsers.items)};return gridNav};Exj.browser.getNameCurrent=function(){if(!this.navCurrent||!this.navCurrent.name){var nameNav=navigator.userAgent;if(!nameNav){nameNav='(UNKNOWN)'}
return nameNav}
nameNav=this.navCurrent.name;return nameNav};Exj.browser.isSupported=function(navNoFound){if(navNoFound===undefined){navNoFound=!1}
if(!this.navCurrent){return navNoFound}
return this.navCurrent.isSupported};Exj.browser.validate=function(){var me=this;for(var i=0,item;i<Exj.Global.dataBrowsers.items.length;i++){item=Exj.Global.dataBrowsers.items[i];if(item.isSupported){var valueSup=Exj.eval(item.js)}
if(Exj.eval(item.js)){me.navCurrent=item;if(item.dataVersions&&item.dataVersions.items.length){for(var j=0,itemVer;j<item.dataVersions.items.length;j++){itemVer=item.dataVersions.items[j];if(itemVer.isVerMin){me.navVerMin=itemVer.ver}
if(Exj.eval(itemVer.js)){me.navVersion=itemVer}}}
break}}
if(Exj.browser.isSupported(!0)){return!0}
var infoHTML='<p style="color:red;">';var nameNav=Exj.browser.getNameCurrent();nameNav='<b>'+nameNav+'</b> ';infoHTML+='El navegador que está usando es: '+nameNav;infoHTML+='<br/>Este navegador no es soportado.';infoHTML+='</p>';infoHTML+='<br/>';infoHTML+='<h1>';infoHTML+='Los siguientes navegadores son soportados o no:';infoHTML+='</h1>';var lblInfo=new Ext.form.Label({html:infoHTML});var getGridNavs=Exj.browser.getGridNavs();var pnlNav=new Ext.Panel({collapsible:!1,layout:'fit',items:getGridNavs});var winNav=Exj.newWindow({title:'Navegador no soportado por '+Exj.TITLE,modal:!0,closable:!1,maximizable:!1,autoHeight:!0,width:Exj.calcWidth(81),buttonAlign:'center',fnCerrar:function(){Ext.get('form-login').dom.Submit.click()},items:[lblInfo,pnlNav]});winNav.addListener('show',function(senderWin){getGridNavs.loadDataNavs()});winNav.show();return!1};Exj.convertFromItemsToString=function(items,fieldText){if(!fieldText){fieldText='text'}
var valuesStr=new Array();for(var i=0,item;i<items.length;i++){item=items[i];valuesStr.push("<b>"+item[fieldText]+"</b>")}
return valuesStr.join('<br/>')};Exj.loadDataGlobal=function(){Exj.Global.LOGIN_USUARIO=Exj.getValueDom('LOGIN_USUARIO');Exj.Global.PermisoAcceso='111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111';if(Exj.Global.dataListLangGlobal){var i=-1;var itemLang;while(++i<Exj.Global.dataListLangGlobal.length){itemLang=Exj.Global.dataListLangGlobal[i];itemLang.compare_sensitive=parseInt(itemLang.compare_sensitive)}}
if(!Exj.Global.infoUser){Exj.Global.infoUser=''}
document.title=Exj.TITLE};Exj.fullScreen=function(element){if(!element){if(document.documentElement){element=document.documentElement}else{element=document.body}}
if(element.webkitRequestFullScreen&&element.ALLOW_KEYBOARD_INPUT){element.webkitRequestFullScreen(element.ALLOW_KEYBOARD_INPUT);return}
var requestMethod=element.requestFullScreen||element.webkitRequestFullScreen||element.mozRequestFullScreen||element.msRequestFullScreen;if(requestMethod){requestMethod.call(element)}else if(typeof window.ActiveXObject!=="undefined"){try{var wscript=new ActiveXObject("WScript.Shell");if(wscript!==null){wscript.SendKeys("{F11}")}}catch(e){}}};Exj.fullScreen2=function(elementxxx){window.moveTo(0,0);if(document.all){top.window.resizeTo(screen.availWidth,screen.availHeight)}else if(document.layers||document.getElementById){if(top.window.outerHeight<screen.availHeight||top.window.outerWidth<screen.availWidth){top.window.outerHeight=screen.availHeight;top.window.outerWidth=screen.availWidth}}};Exj.getURLReportHTML=function(params){var hUrl=new Exj.HUrl({option:'exj_base_reports',controller:'base_reports'});params=params||{};if(params.hUrl){params.nameCmp=params.hUrl.getOption();delete params.hUrl}
if(!params.nameCmp){Exj.moe('No se indicó nombre del componente, para reporte HTML')}
if(params.dataRpt&&Ext.isObject(params.dataRpt)){params.dataRpt=Ext.encode(params.dataRpt)}
return Exj.addParamsHref(hUrl.getActionCustom('dispatch'),params)};Exj.loadDataApp=function(config){var hUrl=new Exj.HUrl({option:'exj_global',controller:'globals'});Exj.submit({url:hUrl.getActionView('getDataGlobal'),isUrlWithExtras:!0,params:config.params,idMask:config.idMask,mask:'Cargando parámetros generales...',showResult:!1,fnSuccess:function(response){Exj.isSessionOut=!1;var dataGlobal=response.data;Exj.Const=dataGlobal.Const;Ext.BLANK_IMAGE_URL=Exj.Const.uriBase+'templates/sy_gym/images/icons/s.gif';Exj._EXJ_APP_COMPANIA=Exj.Const._EXJ_APP_COMPANIA;Exj.TITLE=Exj.Const._EXJ_APP_TITULO;Exj.files.charsMaxNameFile=dataGlobal.infoFile.charsMaxNameFile;Exj.files.maxSizeUpload=dataGlobal.infoFile.maxSizeUpload;Exj.Global.infoUser=dataGlobal.infoUser;Exj.Global.itemsDisplay=dataGlobal.itemsDisplay;Exj.Global.emisor=dataGlobal.emisor;Exj.pg.valuesMap=dataGlobal.pgValuesMap;Exj.Global.dataAccess=dataGlobal.dataAccess;if(Exj.Global.dataAccess){Exj.Global.dataAccess.userIsAdministrator=parseInt(Exj.Global.dataAccess.userIsAdministrator);Exj.Global.dataAccess.userIsSuperAdmin=parseInt(Exj.Global.dataAccess.userIsSuperAdmin);Exj.Global.dataAccess.userIsSuperOrAdmin=parseInt(Exj.Global.dataAccess.userIsSuperOrAdmin)};Exj.Global.dataListLangGlobal=dataGlobal.dataListLangGlobal;Exj.Global.infoAgc=dataGlobal.infoAgc;Exj.Global.dataBrowsers=dataGlobal.dataBrowsers;if(dataGlobal.segTimeoutRep){Exj.Global.timeoutRep=Ext.num(dataGlobal.segTimeoutRep,0);if(Exj.Global.timeoutRep>1){Exj.Global.timeoutRep*=1000}}else{Exj.Global.timeoutRep=0}
if(Exj.Global.infoUser){if(Exj.Global.infoUser.is_main_empresa===undefined){Exj.Global.infoUser.is_main_empresa=0}
Exj.pg.setParams(Exj.Global.infoUser.paramsGen);Exj.Global.infoUser.is_main_empresa=parseInt(Exj.Global.infoUser.is_main_empresa);Exj.Global.infoUser.is_capital=parseInt(Exj.Global.infoUser.is_capital);Exj.Global.infoUser.id_empresa=parseInt(Exj.Global.infoUser.id_empresa);Exj.Global.infoUser.id_moneda=(Exj.Global.infoUser.id_moneda?parseInt(Exj.Global.infoUser.id_moneda):null);Exj.Global.infoUser.id_pais=parseInt(Exj.Global.infoUser.id_pais);Exj.Global.infoUser.id_sit=parseInt(Exj.Global.infoUser.id_sit);Exj.Global.infoUser.enable_debug=parseInt(Exj.Global.infoUser.enable_debug);if(Exj.Global.infoUser.canEditAllUsr=='0'){Exj.Global.infoUser.canEditAllUsr=0}
Exj.isModeDebug=(Exj.Global.infoUser.enable_debug?!0:!1);Exj.Global.infoUser.id_ciu=Exj.Global.infoUser.id_sit;if(Exj.Global.infoUser.id_sit_parent){Exj.Global.infoUser.id_sit_parent=parseInt(Exj.Global.infoUser.id_sit_parent)}
Exj.Global.infoUser.isAgcMain=function(){return(Exj.Global.infoUser.is_main_empresa==1)}}
if(config.success){config.success(dataGlobal);Exj._renderCustomObjExt()}
Exj.browser.validate()}})};Exj._renderCustomObjExt=function(){Ext.MessageBox.buttonText.yes=Exj.Idioma('Si');Ext.MessageBox.buttonText.cancel=Exj.Idioma('Cancelar');Ext.MessageBox.buttonText.ok=Exj.Idioma('Aceptar')};Exj.getPathImageCF=function(){return Exj.Const.uriBase+'images/comprofiler'};Exj.getPathImageUserAvatar=function(){var strAvatar=Exj.Global.infoUser.avatar;if(!strAvatar){return''}
if(parseInt(strAvatar)>0){strAvatar='tn'+strAvatar}
return Exj.getPathImageCF()+'/'+strAvatar};Exj.dataOk=function(){if(Exj.Global.hayError){Exj.moe(Exj.Global.msgError)}
return!Exj.Global.hayError};Exj.rendererFormatDate=function(value){return value?Exj.rendererText(value.dateFormat(Exj.FormatDate)):''};Exj.rendererFormatDateTime=function(value,cfg,r){if(!value){return''}
if(value=='0000-00-00 00:00:00'){return''}
if(!value.dateFormat&&Ext.isString(value)){if(value.length==10){value=Exj.getDateFromServer(value);return value}else if(value.length>10){value=Exj.getDateTimeFromServer(value);return value}}
if(!value.dateFormat){Exj.mou('ERROR. El valor: '+value+' no es una fecha!');return value}
var valueDate=value.dateFormat(Exj.FormatDateTime);if(valueDate=='30/11/00-1 00:00:00'||valueDate=='11/30/ 00:00:00'){return''}
return Exj.rendererText(valueDate)};Exj.URI.dirImages='./templates/sy_gym/images/icons/16x16/';Exj.URI.getIconDownload=function(uriFile,isExtFile){if(!uriFile){uriFile=''}
var extFile='';if(isExtFile){extFile=uriFile}else{extFile=Exj.files.getExtFromNameFile(uriFile)}
var nameImg='download.gif';switch(extFile){case 'pdf':nameImg='pdf.png';break;case 'xls':nameImg='excel1.png';break;case 'xlsx':nameImg='excel2.png';break;case 'doc':case 'docx':case 'rtf':nameImg='word.png';break;case 'html':case 'htm':case 'mht':nameImg='web.png';break;case 'ods':case 'odt':nameImg='openoffice.png';break;case 'txt':nameImg='texto.png';break;case 'png':case 'gif':case 'jpg':case 'jpeg':case 'jfif':case 'tif':case 'tiff':case 'ico':nameImg='image.png';break}
return(Exj.URI.dirImages+nameImg)};Exj.renderURLDownload=function(value){if(!value){return''}
var htmlURL='<a ';htmlURL+='rel="nofollow" ';var codeJsClick="Exj.downLoadFile({url:this.href, isCalledFromLink:true}); return false;";htmlURL+='onclick="'+codeJsClick+'" ';htmlURL+=' title="Download" href="'+value+'">';htmlURL+='<img alt="download" src="'+Exj.URI.getIconDownload(value)+'">';htmlURL+='</a>';return htmlURL};Exj.files.canViewFile=function(nameFile,isExtFile){if(!nameFile){return!1}
var extFile='';if(isExtFile){extFile=nameFile}else{extFile=Exj.files.getExtFromNameFile(nameFile)}
if(!extFile){return!1}
var viewFileOK=!1;switch(extFile){case 'pdf':case 'img':case 'jpg':case 'jpeg':case 'txt':case 'gif':case 'html':case 'htm':case 'jfif':case 'tiff':case 'tif':case 'ico':viewFileOK=!0;break}
return viewFileOK};Exj.isRenderBase=function(strRender){if(!strRender){return!1}
strRender+='';if(!strRender.substring){return!1}
if(strRender.length<=4){return!1}
return(strRender.substring(0,4)=='Exj.')}
Exj.renderEdad=function(value,p,r){if(!value){return''}
var today=new Date();var years=Math.floor((today.getTime()-value.getTime())/(365.25*24*60*60*1000));return years};Exj.renderSiNo=function(value){if(value=='0'){return'No'}
return(value?'Si':'No')};Exj.renderStateEvt=function(value,p,r){if(!value){return''}
if(r&&r.data.color_evt){value=String.format('<span style="color:{0}">{1}</span>',r.data.color_evt,value);return Exj.rendererText(value,p,r)}
return Exj.rendererTextGreen(value,p,r)};Exj.renderDecimalRaw=function(value,color,r){if(value===null||value===''){return''}
value=Ext.num(value,null);if(value===null){return''}
if(color==undefined||!Ext.isString(color)){var autoColor='green';if(value<0){autoColor='red'}
return'<span style="color:'+autoColor+'">'+value+'</span>'}
return'<span style="color:'+color+'">'+value+'</span>'};Exj.renderDecimal2=function(value,color,r){if(value===null||value===''||value===undefined){return''}
if(r&&r.data){if(r.data.isHeader&&!value){return''}}
var valDec=Exj.round(value,2,!0);if(color==undefined||!Ext.isString(color)){var autoColor='green';if(valDec<0){autoColor='red'}
return('<span style="color:'+autoColor+'">'+valDec+'</span>')}
return('<span style="color:'+color+'">'+valDec+'</span>')};Exj.renderEmptyDecimal2=function(value,color,r){if(value===null||value===''){return''}
return Exj.renderDecimal2(value,color,r)};Exj.renderDecimal2ZeroRed=function(value,color,r){if(value===0||value==='0'){return Exj.renderDecimal2(value,'red',r)}
return Exj.renderDecimal2(value,color,r)};Exj.newComboBox=function(cfg){var cmb=new Ext.form.ComboBox(cfg);if(cmb.store){if(cmb.defaultValue&&cmb.store.getCount()){cmb.setValue(cmb.defaultValue)}}
cmb.getRecordSelected=function(){var value=cmb.getValue();if(!value){return null}
return cmb.findRecord(cmb.valueField,value)};cmb.getValueFieldSelected=function(nameField){if(!nameField){nameField='text'}
var r=cmb.getRecordSelected();if(!r){return''}
return r.get(nameField)};return cmb};Exj.addParamsHref=function(href,params){params=params||{};var result=href;var p=Ext.urlEncode(params);if(p.length){result+=((href.indexOf('?')==-1)?'?':'&')+p}
return result};Exj.newButton=function(cfg){if(!cfg){cfg=new Object()}
if(cfg.text==undefined){cfg.text='Sin Título'}
cfg.text=Exj.Idioma(cfg.text);if(cfg.tooltip){cfg.tooltip=Exj.Idioma(cfg.tooltip)}
var btn=new Ext.Button(cfg);if(cfg.iconCls){Exj.applyAccessTask(btn)}
return btn};Exj.newButtonAdd=function(cfg){if(!cfg){cfg=new Object()}
if(cfg.text==undefined){cfg.text='Adicionar'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-new'}
if(!cfg.tooltip){cfg.tooltip='Adiciona un nuevo item'}
return Exj.newButton(cfg)};Exj.newButtonCancel=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Cancelar'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-cancel'}
if(!cfg.tooltip){cfg.tooltip='Cancela algún cambio hecho'}
return Exj.newButton(cfg)};Exj.newButtonSave=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Guardar'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-save'}
if(!cfg.tooltip){cfg.tooltip='Guarda los cambios'}
return Exj.newButton(cfg)};Exj.newButtonDel=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Eliminar...'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-delete'}
if(!cfg.tooltip){cfg.tooltip='Elimina el item seleccionado'}
return Exj.newButton(cfg)};Exj.newButtonDelAll=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Eliminar Todo...'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-delete'}
if(!cfg.tooltip){cfg.tooltip='Elimina todos los items desde la lista, si esto es posible eliminar'}
return Exj.newButton(cfg)};Exj.newButtonCategory=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Categorias...'}
if(!cfg.iconCls){cfg.iconCls='button-category'}
if(!cfg.tooltip){cfg.tooltip='Presenta una lista de categorias'}
return Exj.newButton(cfg)};Exj.newButtonPrint=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Imprimir'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-printer'}
if(!cfg.tooltip){cfg.tooltip='Imprime la lista actual'}
return Exj.newButton(cfg)};Exj.newButtonExpCSV=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Exportar'}
if(!cfg.iconCls){cfg.iconCls='button-export-excel'}
if(!cfg.tooltip){cfg.tooltip='Exporta la lista a un archivo, formato csv'}
return Exj.newButton(cfg)};Exj.newButtonEdit=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Editar'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-edit'}
if(!cfg.tooltip){cfg.tooltip='Edita el item seleccionado'}
return Exj.newButton(cfg)};Exj.newButtonModify=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Modificar'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-edit'}
if(!cfg.tooltip){cfg.tooltip='Modifies the selected item'}
return Exj.newButton(cfg)};Exj.newButtonView=function(cfg){if(!cfg){cfg=new Object()}
if(!cfg.text){cfg.text='Ver...'}
if(!cfg.iconCls){cfg.iconCls='exj-btn-view'}
if(!cfg.tooltip){cfg.tooltip='Presents selected data item'}
return Exj.newButton(cfg)};Exj.rendererTextIcon=function(text,icon){if(!icon){return text}
var html='<img class="x-menu-item-icon '+icon+'" src="'+Ext.BLANK_IMAGE_URL+'">';html+=text;return html};Exj.rendererTextLastChange=function(value,p,r){if(!value||value=='30/11/-0001 00:00'){return''}
return value};Exj.renderPercent=function(value,p,r){return Exj.getPercent(value,2,'blue')};Exj.renderMoney=function(value,p,r,color){if(value==null){return''}
if(value==''){return''}
if(isNaN(value)){return''}
if(color===undefined){color='green';if(value<0){color='red'}}else{if((color!='green')&&(color!='red')&&color!='blue'){color='green'}}
var valRet=Exj.renderDecimal2(value,color);if(r.data.sim_moneda){valRet+=' '+r.data.sim_moneda}else{if(r.data.id_moneda){valRet+=' '+Exj.mon.getSimbolo(r.data.id_moneda)}}
return valRet};Exj.renderMoneyEmpty=function(value,p,r,color){if(!value){return''}
return Exj.renderMoney(value,p,r,color)};Exj.renderStateActive=function(value,p,r){var color='blue';var txt='SI';if(value==0){txt='NO';color='red'}
return('<span style="color:'+color+'">'+txt+'</span>')};Exj.cmbOpcSiNo=function(config){if(config.fieldLabel==undefined){config.fieldLabel='Activo'}
if(config.clearable==undefined){config.clearable=!1}
if(config.width==undefined){config.width=(config.clearable?90:60)}
config.listWidth=config.width+12;config.data=new Array();var index=-1;if(config.clearable){config.data[++index]=[-1,Exj.Idioma('TODO')]};config.data[++index]=[1,Exj.Idioma('SI')];config.data[++index]=[0,Exj.Idioma('NO')];config.valueDefault=config.value;var _retCmb=Exj.newComboArray(config);return _retCmb};Exj.renderMoneyRed=function(value,p,r){var valRet=Exj.renderDecimal2(value,'red');var dataRender=r;if(r.data!=undefined){dataRender=r.data}
if(dataRender.sim_moneda){valRet+=' '+dataRender.sim_moneda}else{if(dataRender.id_moneda){valRet+=' '+Exj.mon.getSimbolo(dataRender.id_moneda)}}
return valRet};Exj.renderMoneyGreen=function(value,p,r){return Exj.renderMoney(value,p,r,'green')};Exj.renderMoneyCustom=function(value,id_moneda,color){if(color==undefined){color='green'}
var valRet=Exj.renderDecimal2(value,color);if(id_moneda){valRet+=' '+Exj.mon.getSimbolo(id_moneda)}
return valRet};Exj.renderNumOrder=function(value){if(!value||value=='0'){return''}
return value};Exj.renderNumberRed=function(value){if(value===null){return''}
if(value>=0){return value}
return('<span style="color: red">'+value+'</span>')};Exj.renderNumberBlue=function(value){if(value===null){return''}
if(value<0){return Exj.renderNumberRed(value)}
return('<span style="color:blue;">'+value+'</span>')};Exj.renderNumberBlue2=function(value){if(value===null){return''}
if(value<=0){return('<span style="color: red">'+value+'</span>')}
return('<span style="color:blue;">'+value+'</span>')};Exj.showMessageBox=function(cfgMsg,p2,p3){cfgMsg=Ext.apply({width:Exj.calcWidthMin(33,333),buttons:Ext.MessageBox.OK,title:Exj.TITLE,icon:Ext.MessageBox.WARNING},cfgMsg);cfgMsg.modal=!0;cfgMsg.msg=Exj.Idioma(cfgMsg.msg);if(cfgMsg.msg){if(cfgMsg.msg.indexOf("\n")>0){cfgMsg.msg=cfgMsg.msg.split("\n").join('<br/>')}}
var _fnOk=null;var _title='';if(p2||p3){if(p2){if(p2&&Ext.isFunction(p2)){_fnOk=p2}else{_title=p2}}
if(p3){if(p3&&Ext.isFunction(p3)){_fnOk=p3}else{_title=p3}}}
if(_title){cfgMsg.title=Exj.Idioma(_title)}
if(_fnOk){cfgMsg.fn=_fnOk}
return Ext.MessageBox.show(cfgMsg)};Exj.moe=function(msg,title,fn){return Exj.showMessageBox({msg:msg,icon:Ext.MessageBox.ERROR,title:'ERROR'},title,fn)};Exj.moi=function(msg,title,fn){return Exj.showMessageBox({msg:msg,icon:Ext.MessageBox.INFO,title:'INFORMACION'},title,fn)};Exj.mow=function(msg,title,fn){return Exj.showMessageBox({msg:msg,icon:Ext.MessageBox.WARNING,title:'ADVERTENCIA'},title,fn)};Exj.mou=function(msg,title){if(!title){title=Exj.TITLE}
return Exj.util.msg(title,'{0}.',Exj.Idioma(msg))};Exj.msg=function(title,msg){Ext.Msg.show({title:title,msg:msg,minWidth:200,modal:!0,icon:Ext.Msg.INFO,buttons:Ext.Msg.OK})};Exj.rendererText=function(value,p,record){if(!value){return''}
if(value==null){return''}
return String.format('<span class="exj-render-text" >{0}</span>',value)};Exj.rendererTextMemo=function(value,p,record){if(value&&value.replace){value=value.replace('\n',' ')}
return Exj.rendererText(value,p,record)};Exj.rendererTextUser=function(value,p,r){return Exj.rendererText(r.data.name+' ('+r.data.username+')',p,r)};Exj.rendererTextHighlight=function(value,p,record){if(!value){return''}
var text=Exj.rendererText(value,p,record);text='<span style="color:blue;">'+text;text+='</span>';return text};Exj.rendererTextColor=function(value,p,record){if(!value){return''}
var color='';if(record){color=record.data.color}else{color=p;if(color&&!color.length){color=''}}
var text=Exj.rendererText(value,p,record);if(color){text='<span style="color:'+color+';">'+text;text+='</span>'}
return text};Exj.rendererTextGreen=function(value,p,record){if(!value){return''}
value='<span style="color:green;">'+value;value+='</span>';return Exj.rendererText(value,p,record)};Exj.rendererTextBlue=function(value,p,record){if(!value){return''}
value='<span style="color:blue;">'+value;value+='</span>';return Exj.rendererText(value,p,record)};Exj.rendererTextRed=function(value,p,record){if(!value){return''}
value='<span style="color:red;">'+value;value+='</span>';return Exj.rendererText(value,p,record)};Exj.rendererTextSiNo=function(value,p,record){if(value=='0'){value=0}
var color=(value?'green':'red');value=(value?'Si':'No');return('<span style="color:'+color+';">'+value+'</span>')};Exj.rendererTextNoSi=function(value,p,record){if(value=='0'){value=0}
var color=(value?'red':'green');value=(value?'Si':'No');return('<span style="color:'+color+';">'+value+'</span>')};Exj.renderBoolNY=function(value,p,record){if(value=='0'){value=0}
var color=(value?'red':'green');value=(value?'S':'N');return('<span style="color:'+color+';">'+value+'</span>')};Exj.fixGridHeadersCells=function(grid,params){var bgColor=params.bgColor,color=params.color;if(!bgColor&&!color){return}
function _fixHeaders(senderView){if(!senderView){senderView=grid.getView()}
var colCount=senderView.cm.getColumnCount();for(i=0,styleCell=null;i<colCount;i++){styleCell=senderView.getHeaderCell(i).style;if(color){styleCell.color=color}
if(bgColor){styleCell.background='none repeat scroll 0 0 '+bgColor}
if(params.fnEachCol){params.fnEachCol(styleCell,i)}}};grid.getView().addListener('refresh',function(senderView){_fixHeaders(senderView)});grid.addListener('viewready',function(senderGrid){_fixHeaders(senderGrid.getView())})};Exj.DataServerFromDataTable=function(store,nameData){var _data=Exj.getDatas(store);var fields='';var items='';var error='';if(!_data){return null}
if(!nameData){error=('No existe el objeto nameData.');return null}
fields=getFields();items=getItems();function _getData(){var retData=null;if(!_data){return retData}
var i=-1;while(++i<_data.length){var d=_data[i];if(d.nameData==nameData){retData=d;break}}
if(retData==null){error=('No existe el nameData: '+nameData)}
return retData}
function getFields(){var retFields=null;var data=_getData();if(!data){return retFields}
retFields=data.Columns;return retFields}
function getItems(){var retItems=null;var data=_getData();if(!data){return retItems}
retItems=data.Rows;if(!retItems){return retItems}
var i=-1;var retItemsArray=new Array();while(++i<retItems.length){var item=retItems[i];retItemsArray[i]=item.Items}
return retItemsArray}
function getItemsWithFields(){var retItems=new Array();var data=_getData();if(!data){return retItems}
if(!fields||!items){return retItems}
var i=-1;while(++i<items.length){var item=items[i];var newObj2=new Object();var j=-1;var newObj='{';while(++j<fields.length){var field=fields[j];newObj2.$field=item[j];if(newObj!='{'){newObj+=','}
newObj+='"'+field+'": "'+item[j]+'"'}
newObj+='}';retItems[i]=newObj2}
var recordsExt=new Object();recordsExt.records=retItems;return recordsExt}
return{fields:fields,items:items,error:error,getItemsWithFields:getItemsWithFields}};Exj.getObjFromStore=function(store){if(!store){Exj.moe('No existe el objeto store.');return null}
if(!Exj.isSuccessResponse(store)){return null}
var json=store.reader.jsonData;var _data=json.obj;if(_data===undefined){Exj.moe('El servidor no ha devuelto el objeto obj, según la estructura.');return null}
return _data};Exj.getDatas=function(store){if(!store){Exj.moe('No existe el objeto store.');return null}
var json=store.reader.jsonData;if(!json.success){if(json.msgError){Exj.moe(json.msgError)}else{Exj.moe('El servidor no informa la razón de la falla.')}
return null}
var _data=json.data;if(!_data){_data=json.topics}
if(!_data){Exj.moe('El servidor no retornó datos por defecto.');return null}
return _data};Exj.getDataReportes=function(respuesta){if(!respuesta){Exj.moe('No se ha obtendo respuesta para el reporte.');return''}
var rep=respuesta.data[0];if(rep.msgError){Exj.moe('Error obteniendo datos para el reporte: '+rep.msgError);return''}
if(!rep.nameReport){Exj.moe('Para presentar el reporte, se requiere el nombre del reporte (nameReport) en la estructura: Exj.Tables.Reporte.Reportes');return''}
if(!rep.task){Exj.moe('Para presentar el reporte, se requiere la tarea del reporte (task)<br />En la estructura: Exj.Tables.Reporte.Reportes');return''}
return rep};Exj.showDownLoadFileCustom=function(params){if(!params.title){params.title='de Archivos'}
params.title=Exj.Idioma('Descargas')+' - '+Exj.Idioma(params.title);var win;win=new Exj.WinSubmit({title:params.title,width:540,height:210,withButtonOk:!1,textCancel:'Cancelar'},{labelWidth:45});var i;var btn;if(params.buttonsOpen){i=-1;while(++i<params.buttonsOpen.length){btn=params.buttonsOpen[i];if(!btn.url){alert('Error. No se indicó url para descarga rápida');continue}
var _btnx=Exj.newButton({text:btn.text+'...',iconCls:'button-import',tooltip:btn.tooltip,handler:function(sender,e){if(!sender._url){alert('Error. No se indicó url para descarga rápida!!!');return}
Exj.downLoadFile({url:sender._url});win.hide()}});_btnx._url='download/'+btn.url;win.addToForm(_btnx)}}
if(params.buttonsSubmit){i=-1;while(++i<params.buttonsSubmit.length){btn=params.buttonsSubmit[i];var _btnEmpty=Exj.newButton({text:btn.text+' (Vacío)...',iconCls:'button-import',tooltip:btn.tooltip,handler:function(sender,e){Exj.downLoadFile({url:'index3.php?option=exj_base_download&task=downloadCustom'+'&verApp='+Exj.verApp+'&id_cou_current='+Exj.Global.infoUser.id_pais+'&withDataSample=0',params:sender._params});win.hide()}});_btnEmpty._params=btn.params;var _btnSample=Exj.newButton({text:btn.text+' (Ejemplo)...',iconCls:'button-import',tooltip:btn.tooltip,handler:function(sender,e){Exj.downLoadFile({url:'index3.php?option=exj_base_download&task=downloadCustom'+'&verApp='+Exj.verApp+'&id_cou_current='+Exj.Global.infoUser.id_pais+'&withDataSample=1',params:sender._params});win.hide()}});_btnSample._params=btn.params;var pDownLoadEmptySample=Exj.newPanelCols({title:'Formats discharge',items:[{columnWidth:0.50,border:!1,items:[_btnEmpty]},{columnWidth:0.50,border:!1,items:[_btnSample]}]});win.add(pDownLoadEmptySample)}}
win.show()};Exj.downLoadFile=function(paramsDownload){paramsDownload.params=paramsDownload.params||{};var isURLRaw=(paramsDownload.url?!0:!1);if(paramsDownload.isCalledFromLink===undefined){paramsDownload.isCalledFromLink=!1}
if(!paramsDownload.url&&paramsDownload.idFile){var hUrlDownload=new Exj.HUrl({option:'exj_basedownload',controller:'basedownload'});paramsDownload.url=hUrlDownload.getActionCustom('dispatch');paramsDownload.url+='&'+'isRestFul=false';paramsDownload.params.idFile=paramsDownload.idFile;paramsDownload.params.idFull=(paramsDownload.idFull?1:0);paramsDownload.params.entry=paramsDownload.entry;paramsDownload.params.fileName=paramsDownload.fileName;paramsDownload.params.canView=(paramsDownload.canViewFile?1:0)}
if(!paramsDownload.url){Exj.moe('No se ha enviado el url, para descarga del archivo');return}
function _rebuildURLForDownload(){if(!paramsDownload.idFile){return!1}
isURLRaw=!1;var hUrlDownload=new Exj.HUrl({option:'exj_basedownload',controller:'basedownload'});paramsDownload.url=hUrlDownload.getActionCustom('dispatch');paramsDownload.url+='&'+'isRestFul=false';paramsDownload.params.idFile=paramsDownload.idFile;paramsDownload.params.idFull=(paramsDownload.idFull?1:0);paramsDownload.params.entry=paramsDownload.entry;paramsDownload.params.fileName=paramsDownload.fileName;paramsDownload.params.canView=!1;paramsDownload.canViewFile=!1;return!0};if(paramsDownload.canViewFile===undefined){if(paramsDownload.fileExt){paramsDownload.canViewFile=Exj.files.canViewFile(paramsDownload.fileExt,!0)}else{paramsDownload.canViewFile=Exj.files.canViewFile(paramsDownload.url)}
paramsDownload.params.canView=(paramsDownload.canViewFile?1:0)}
function _getURLFull(){var urlFull=paramsDownload.url;if(paramsDownload.params&&!isURLRaw){var paramsText=paramsDownload.params;if(Ext.isObject(paramsText)){paramsText=Ext.urlEncode(paramsText)}
if(paramsText){urlFull+='&'+paramsText}}
return urlFull};function _getHTMLLinkDownload(){var htmlLinkDownload='<a ';htmlLinkDownload+=' href="'+_getURLFull()+'"';htmlLinkDownload+='>';htmlLinkDownload+='Descargar';htmlLinkDownload+='</a>';return htmlLinkDownload};function _callDownloadFromLinkHidden(){var linkDownload=document.getElementById("exj_download");linkDownload.href=_getURLFull();linkDownload.click()};function _callDownloadFromIFrameDinamyc(){try{var objFramex=Ext.get('tevDownloadIframe');if(objFramex){Ext.destroy(objFramex)}}catch(e){}
Ext.DomHelper.append(document.body,{tag:'iframe',id:'tevDownloadIframe',frameBorder:0,width:0,height:0,css:'display:none;visibility:hidden;height:0px;',src:_getURLFull()})};function _callDownloadFromWinOpen(){window.open(_getURLFull(),'_blank')};function _showWinDownload(){var winDownload,frmDownload;_rebuildURLForDownload();var txfFileName=Exj.newTextField({fieldLabel:'Nombre del Archivo',value:paramsDownload.fileName,allowBlank:!1,blankText:'El nombre del archivo no puede ser vacio',anchor:'99%',vtype:'textnamefile',maxLength:66});frmDownload=new Ext.FormPanel({labelWidth:111,frame:!0,bodyStyle:'padding:3px 3px 0',autoWidth:!0,autoHeight:!0,onSubmit:Ext.emptyFn,submit:function(){var txtFileName=txfFileName.getValue().trim();if(!txtFileName){txfFileName.setValue('');Exj.moi('Ingrese un nombre de archivo para descargar',function(){txfFileName.focus()});return!1}
paramsDownload.params.fileName=txtFileName;frmDownload.getForm().getEl().dom.action=_getURLFull();frmDownload.getForm().getEl().dom.method='POST';frmDownload.getForm().getEl().dom.submit();return!0},items:[txfFileName,{layout:'column',items:[{columnWidth:0.6,layout:'form',defaultType:'textfield',labelWidth:30,items:[{fieldLabel:'Type',value:Exj.files.exts.getDesc(paramsDownload.fileExt),anchor:'99%',disabled:!0}]},{columnWidth:0.4,layout:'form',defaultType:'textfield',labelWidth:45,items:[{fieldLabel:'Size',value:paramsDownload.fileSize,anchor:'99%',disabled:!0}]}]}],buttons:[{text:'Descargar',icon:Exj.URI.getIconDownload(paramsDownload.fileExt,!0),tooltip:'Permite descargar el archivo. Puede cambiar el nombre del archivo si lo desea',handler:function(){var bf=frmDownload.getForm();if(!bf.isValid()){Exj.moi('Existen errores en la ventana de descarga.<br/>Revise por favor...');return}
if(bf.submit()===!1){return}
winDownload.close()}},{text:'Cerrar',iconCls:'exj-btn-cancel',tooltip:'Cancels the download',handler:function(){winDownload.close()}}]});winDownload=new Ext.Window({layout:'fit',title:'Descargar Archivo',width:Exj.calcWidth(45),autoHeight:!0,closeAction:'close',plain:!0,items:[frmDownload]});winDownload.show()};function _getFileName(){var fileName=paramsDownload.fileName;if(!fileName){fileName='documento'}
return fileName};function _viewFileWithWindow(){if(Exj._winNavigator&&Exj._winNavigator.close){Exj._winNavigator.close()}
Exj._winNavigator=window.open(_getURLFull(),'_blank')};if(paramsDownload.returnURL){return _getURLFull()}
if(!paramsDownload.canViewFile){if(Ext.isIE||Ext.isSafari){if(!paramsDownload.isCalledFromLink){_showWinDownload();return}}
_callDownloadFromLinkHidden();return}
if(Ext.isSafari&&!paramsDownload.isCalledFromLink){_showWinDownload();return}
_viewFileWithWindow()};Exj.exportFileData=function(config){if(!config._key_export){Exj.moe('No se especificó _key_export')}
var params=new Array();var i=-1;params[++i]='_key_export='+config._key_export;if(config.filename){config.filename+='_'+config.grid.getStore().getTotalCount();params[++i]='filename='+config.filename}
if(config.addDataGlobal===undefined){config.addDataGlobal=!1}
var infoCols=Exj.getColsFromGrid(config.grid);params[++i]='JsonInfoCols='+Ext.encode(infoCols);var url=Exj.addParamsURL('index3.php?option=exj_base_export&task=exportCSV',config.addDataGlobal);Exj.downLoadFile({url:url,params:params})};Exj.getDataDefault=function(store,index){var _data=Exj.getDatas(store);if(!_data){return null}
if(!_data.length){return _data}
if(!index){index=0}else{if(index>=_data.length){index=_data.length-1}}
if(index<0){index=0}
_data=_data[index];_data=_data.dataServer;if(!_data){Exj.moe('El servidor debe de devolver en la estrucutura: dataServer.');return null}
return(_data)};Exj.showDataBuffer=function(strBuffer){if(!strBuffer){return}
if(strBuffer.length>3){if(strBuffer.indexOf("\n")>0){strBuffer=strBuffer.split("\n").join('<br/>')}
Exj.showHTML({title:'Modo depuración - Salida Buffer',html:strBuffer})}};Exj.isSessionOut=!1;Exj.requireLoginUser=function(data){if(!data){return!1}
if(data.ini_session!==undefined){if(data.ini_session){Exj.isSessionOut=!0;if(!data.msg){Ext.get('form-login').dom.Submit.click()}else{Ext.MessageBox.show({title:'Login',closable:!1,msg:Exj.Idioma(data.msg),buttons:Ext.MessageBox.OK,fn:function(){Ext.get('form-login').dom.Submit.click()},icon:Ext.MessageBox.INFO,modal:!0})}
return!0}}
return!1};Exj.isOffline=function(obj){if(!obj){return!1}
if(obj._offline!=undefined){if(obj._offline){Exj.forceExitSystem(obj._offline_message);return!0}}
return!1};Exj.reloadDocument=function(){if(document.location.href&&document.location.href.replace){document.location=document.location.href.replace('#','')}else{document.location=document.location}};Exj.forceExitSystem=function(offline_message){if(!offline_message){offline_message='Sitio esta fuera de línea, por el Administrador'}
offline_message='<span style="color:red;">'+offline_message;offline_message+='</span>';Ext.Msg.show({title:Exj.TITLE,msg:offline_message,buttons:Ext.Msg.OK,fn:function(btn){Ext.get('form-login').dom.Submit.click()},animEl:'elId',icon:Ext.MessageBox.INFO})};Exj.addLoadException=function(component){if(!component){return}
if(component.store&&component.store.proxy){Exj.loadException(component.store)}};Exj.loadExceptionProxy=function(proxyStore){proxyStore.addListener("loadException",function(sender,options,arg,e){if(e){Exj.moe('Ocurrió un error. Razón: '+e);return}
Exj.moe('Conexión falló con el servidor. Asegúrese que tenga acceso a el servidor.')})};Exj.loadException=function(store,fnLoadData){store.addListener("exception",function(misc){if(!Exj.isSuccessResponse(store)){return}
Exj.moe('El servidor está ocupado.<br/>Inténtelo más tarde.')});store.addListener("load",function(sender,records,options){if(!Exj.isSuccessResponse(store)){return}
if(fnLoadData){var jsData=store.reader.jsonData;fnLoadData(sender,records,options,jsData)}})};Exj.getModifiedRecords=function(store,msgSinRows){var rows=null;if(!store){return rows}
rows=store.getModifiedRecords();if(rows.length==0){if(!msgSinRows){msgSinRows='No se han modificado registros.'}
Exj.mou(msgSinRows);return null}
return rows};Exj.getResultSubmit=function(resultData,titleResult){var r='';if(resultData.result==undefined){var rJson=resultData.responseText;if(rJson==undefined){Exj.moe('Error de programación, no se ha pasado un objeto de respuesta: store o submit');return null}
r=Ext.util.JSON.decode(rJson)}else{r=resultData.result}
var data=r.data;if(data==undefined){Exj.moe('Error de programacion. Se debe servir los datos con las estructura: DataRequestSubmit');return null}
if(!titleResult){titleResult=''}
var res=titleResult;if(data==null){if(res){res+='<br />'}
res+='Datos han sido guardados!!!';return res}
if(data.length==undefined){return data}
var i=-1;while(++i<data.length){if(res){res+='<br />'}
res+=data[i]}
return res};Exj.msgPrompt=function(params){params=Ext.apply({title:Exj.TITLE,msg:'',scope:this,multiline:!1,minWidth:Ext.MessageBox.minPromptWidth,value:'',allowBlank:!0},params);params.prompt=!0;params.buttons=Ext.MessageBox.OKCANCEL;params.fn=function(btn,text){if(btn=='ok'){text=text.trim();if(!params.allowBlank&&!text){Exj.msgPrompt(params);return}
if(params.validate&&!params.validate(text)){return}
if(params.fnOk){params.fnOk(text)}}};Ext.MessageBox.show(params)};Exj.eventComboClearFilterBlur=function(params){params.objComboBox.addListener('blur',function(sender){if(sender.store.isFiltered()){sender.store.clearFilter();var _r=Exj.getRecordSelectedFromCombo(sender);if(_r){if(_r.data.text!=sender.getRawValue()){Exj.mou('No existe: <p style="color:blue;">'+sender.getRawValue()+'</p>',sender.fieldLabel);sender.setValue(_r.data.value)}}}
if(params.fnOnBlur){params.fnOnBlur(sender)}})};Exj.selectValueDefaultFromCombo=function(params){if(params.objComboBox.store.isFiltered()){params.objComboBox.store.clearFilter()};params.objComboBox.store.each(function(r){if(r.data.is_default==1){params.objComboBox.setValue(r.data.value);return!1}})};Exj.selectFromCombo=function(objComboBox,index){if(index==undefined){index=0}
if(objComboBox.store.getCount()==0){return!1}
if(objComboBox.store.getCount()<=index){index=objComboBox.store.getCount()-1}
var firstValue=objComboBox.store.getAt(index);if(firstValue){objComboBox.setValue(firstValue.data.value)}
return!0};Exj.msgQuestion=function(config){if(config.title==undefined){config.title=Exj.TITLE}
if(config.msg==undefined){config.msg='Sure'}
config.msg+='?';if(config.buttons==undefined){config.buttons=Ext.Msg.YESNO}
if(config.icon==undefined){config.icon=Ext.MessageBox.QUESTION}
if(config.fn==undefined){config.fn=function(buttonId){if(buttonId=='yes'){if(config.fnYes){config.fnYes()}}else{if(config.fnNo){config.fnNo()}}}}
Ext.Msg.show(config)};Exj.ListLabel=function(config){if(!config){config=new Object()}
var _prefixID='_es_';var _prefixIDLabel='_es_label_';if(config.title==undefined){config.title=''}
if(config.fieldLabel==undefined){config.fieldLabel=''}
if(config.styleItems==undefined){config.styleItems=''}
if(config.widthLabel==undefined){config.widthLabel=60}
if(config.value==undefined){config.value=''}
var attrX;widthCol1=config.widthLabel+'px';var html='';html+='<table class="x-grid3-row-table" cellspacing=0 cellpadding=0 border=0 >';if(config.title){html+='<tr>';html+='<thead align="center" colspan="2">';html+=Exj.Idioma(config.title);html+='</thead>';html+='</tr>'}
var i=-1;var itemLabel;var idItem;var idLabel;var elemFieldLabel;while(++i<config.items.length){itemLabel=config.items[i];if(!itemLabel.style){itemLabel.style=config.styleItems}
if(itemLabel.value==undefined){itemLabel.value=''}
if(!itemLabel.name){itemLabel.name='itemList'+i}
if(itemLabel.align==undefined){if(config.alignDefault){itemLabel.align=config.alignDefault}else{itemLabel.align='right'}}
idItem=getNameLabel(itemLabel.name);idLabel=getNameFieldLabel(itemLabel.name);html+='<tr>';html+='<td>';elemFieldLabel='<span id="'+idLabel+'">'+Exj.Idioma(itemLabel.label)+'</span>';if(itemLabel.label){html+='<div class="x-form-item" style="color:black; width:'+widthCol1+';">'+elemFieldLabel+':</div>'}else{html+='<div class="x-form-item" style="color:black; width:'+widthCol1+';">'+elemFieldLabel+'</div>'}
html+='</td>';attrX='style="'+itemLabel.style+' text-align:'+itemLabel.align+';"';html+='<td '+attrX+'  valign="top">';html+='<div id="'+idItem+'">'+itemLabel.value+'</div>';html+='</td>';html+='</tr>'}
html+='</table>';if(config.html==undefined){config.html=''}
config.html+=html;var _lblList=new Ext.form.Label(config);function getNameLabel(nameLbl){return _prefixID+nameLbl};function getNameFieldLabel(nameLbl){return _prefixIDLabel+nameLbl};_lblList.setValue=function(nameLabel,value){var node=Exj.getDom(getNameLabel(nameLabel));if(!node){return!1}
node.innerHTML=value;return!0};_lblList.getValue=function(nameLabel){var valueRet='';var node=Exj.getDom(getNameLabel(nameLabel));if(!node){return valueRet}
valueRet=node.innerHTML;return valueRet};_lblList.setFieldText=function(nameLabel,text){if(!text){}
var node=Exj.getDom(getNameFieldLabel(nameLabel));if(!node){return!1}
node.innerHTML=Exj.Idioma(text);return!0};_lblList.setValueAll=function(value){var i=-1;var itemLabel;while(++i<config.items.length){itemLabel=config.items[i];var node=Exj.getDom(getNameLabel(itemLabel.name));if(node){node.innerHTML=value}}
return!0};return _lblList};Exj.inputBox=function(config){config=config||{};if(!config.fnOk){Exj.moe('No se ha pasado la función para el proceso: InputBox, el textPrompt es:'+config.textPrompt);return}
if(config.titleMsg==undefined){config.titleMsg=Exj.TITLE}
if(config.valueDef==undefined){config.valueDef=''}
Ext.Msg.prompt(config.titleMsg,config.textPrompt,function(btn,text){if(btn=='ok'){text=text.trim();if(!text){Exj.mou('Proceso está cancelado...');return}
config.fnOk(text)}},this,!1,config.valueDef)};Exj.inputBoxNumeric=function(params){params=Ext.apply({title:Exj.TITLE,msg:'',valueDef:'',scope:this,multiline:!1,reShowInvalid:!1,isIntValue:!1},params);if(!params.fnOk){Exj.moe('No se ha pasado la función para el proceso: InputBoxNumeric:'+params.msg);return}
Ext.Msg.prompt(params.title,params.msg,function(btn,text){if(btn=='ok'){text=text.trim();if(!text){if(params.fnCancel){params.fnCancel(btn,text,!1)}
return}
var num=0,msgInvalid='';if(params.isIntValue){num=parseInt(text)}else{num=parseFloat(text)}
if(isNaN(num)){msgInvalid='Debe ingresar un valor numérico.'}else if(params.minValue!==undefined&&num<params.minValue){msgInvalid='Valor mínimo es: '+params.minValue}else if(params.maxValue!==undefined&&num>params.maxValue){msgInvalid='Valor máximo es: '+params.maxValue}
if(msgInvalid){Exj.mou(msgInvalid,params.title);if(params.reShowInvalid){Exj.inputBoxNumeric(params)}else{if(params.fnCancel){params.fnCancel(btn,text,!1)}}
return}
params.fnOk(num)}else{if(params.fnCancel){params.fnCancel(btn,text,!0)}}},params.scope,params.multiline,params.valueDef)};Exj.newWinSubmitDel=function(cfg,cfgFormPanel){cfg=cfg||{};cfg=Ext.apply({isRestFul:!1,methodToSubmit:'POST',width:Exj.calcWidth(45)},cfg);cfg.textOk='Eliminar';cfg.iconClsOk='app-btn-ok';cfg.waitMsg='Eliminando';if(!cfg.urlSubmit){Exj.moe('No se ha indicado urlSubmit en: Exj.newWinSubmitDel','ERROR DE IMPLEMENTACION')}
return new Exj.WinSubmit(cfg,cfgFormPanel)};Exj.WinSubmit=Ext.extend(Ext.Window,{constructor:function(config,cfgFormPanel){config=Ext.apply({modal:!0,urlSubmit:'',hUrl:null,idValue:0,recordEditable:null,maximizable:!1,width:270,closable:!0,autoHeight:!0,plain:!0,layout:'fit',autoScroll:!0,closeAction:'close',bbar:new Array(),waitMsg:'Guardando',withButtonOk:!0,withButtonCancel:!0,textOk:'Guardar',textCancel:'Cancelar',iconClsOk:'app-btn-save',iconClsCancel:'exj-btn-cancel',buttonsExtras:null,tooltipOk:'',isButtonsOkCancel:!1,fnIsValid:null,fnSuccess:null,fnFailure:null,fnGetDataChangesExtras:null,methodToSubmit:'',onlyModeLocal:!1,onlyEnabledDataChange:!1,isReadOnlyAccess:!1,isSuccessActionReset:!1,fnSuccessActionReset:null,isSuccessActionNone:!1,reloadStoreAppMainActionAll:!1},config);config.title=Exj.Idioma(config.title);var me=this;this.isSuccessActionReset=config.isSuccessActionReset;this.fnSuccessActionReset=config.fnSuccessActionReset;if(this.fnSuccessActionReset&&Ext.isFunction(this.fnSuccessActionReset)){this.isSuccessActionReset=config.isSuccessActionReset=!0}
this.isSuccessActionNone=config.isSuccessActionNone;this.reloadStoreAppMainActionAll=config.reloadStoreAppMainActionAll;this.onlyModeLocal=config.onlyModeLocal;this.onlyEnabledDataChange=config.onlyEnabledDataChange;this.isReadOnlyAccessSubmit=config.isReadOnlyAccess;if(config.isReadOnlyAccess){config.withButtonOk=!1;config.textOk='No permitido!';config.textCancel='Cerrar';config.iconClsOk='app-btn-ok'}
if(config.isButtonsOkCancel){config.textOk='Aceptar';config.textCancel='Cancelar';config.iconClsOk='app-btn-ok'}
this._btnOk=null;if(config.withButtonOk){this._btnOk=Exj.newButton({text:config.textOk,disabled:!1,tooltip:config.tooltipOk,iconCls:config.iconClsOk});config.bbar.push(this._btnOk)}
if(config.timeOutSec){this._timeOutSec=config.timeOutSec}
if(config.buttonsExtras&&config.buttonsExtras.length>0){config.bbar.push('-');for(var indexBtn=0,btnExtra;indexBtn<config.buttonsExtras.length;indexBtn++){btnExtra=config.buttonsExtras[indexBtn];if(!btnExtra.handler||!Ext.isFunction(btnExtra.handler)){if(!btnExtra.menu){continue}}
btnExtra.autoCloseWin=(btnExtra.autoCloseWin?!0:!1);if(btnExtra.menu){for(var indexMnuBtn=0,mnuExtra;indexMnuBtn<btnExtra.menu.length;indexMnuBtn++){mnuExtra=btnExtra.menu[indexMnuBtn];mnuExtra._rootHandler=(mnuExtra.handler?mnuExtra.handler.createCallback(mnuExtra.text):null);if(mnuExtra.autoCloseWin===undefined){mnuExtra.autoCloseWin=btnExtra.autoCloseWin}
mnuExtra.handler=function(senderBtnMnu,e){if(senderBtnMnu.autoCloseWin){me.closeWinSubmit()}
if(senderBtnMnu._rootHandler){senderBtnMnu._rootHandler(senderBtnMnu,e,me)}}}
config.bbar.push(btnExtra)}else{config.bbar.push(Exj.newButton({text:btnExtra.text,tooltip:btnExtra.tooltip,iconCls:btnExtra.iconCls,autoCloseWin:btnExtra.autoCloseWin,handler:function(senderBtnExtra,e){if(senderBtnExtra.autoCloseWin){me.closeWinSubmit()}
btnExtra.handler(senderBtnExtra,e,me)}}))}}}
this._btnCancel=null;if(config.withButtonCancel){this._btnCancel=Exj.newButton({text:config.textCancel,iconCls:config.iconClsCancel,handler:function(){if(me.fnBeforeCancel&&Ext.isFunction(me.fnBeforeCancel)){if(me.fnBeforeCancel(this,me)===!1){return}}
me.closeWinSubmit()}});config.bbar.push(this._btnCancel)}
if(!config.idValue&&config.recordEditable&&config.recordEditable.id){config.idValue=config.recordEditable.id}
this.isNew=(config.idValue?!1:!0);if(config.idValue&&config.idValue<0){this.isNew=!0}
if(!config.title&&config.nameEntity){config.title=(this.isNew?'Crear':'Editar')+' '+config.nameEntity;if(config.isReadOnlyAccess){config.title=config.nameEntity+' (Solo Lectura)'}}
if(config.waitMsg&&config.nameEntity){config.waitMsg+=' '+config.nameEntity}
Exj.WinSubmit.superclass.constructor.call(this,config);this._initWin(cfgFormPanel)},onEnterTab:function(keyCode,e){e.stopEvent();if(!e.target||!e.target.name){return}
var basicForm=this.getBasicForm();var fieldCurrent=basicForm.findField(e.target.name);if(!fieldCurrent){return}
if(!fieldCurrent.isValid()){Exj.mou(fieldCurrent.getErrors()[0],'ERROR');return}
var nodes=Ext.query('input,textarea',this.getEl().dom),nodeFocus=null;if(nodes&&nodes.length){for(var i=0,nodex;i<nodes.length;i++){nodex=nodes[i];if(nodex.name==e.target.name){nodeFocus=nodes[i+1];if(nodeFocus&&!nodeFocus.disabled){break}}}}
if(nodeFocus&&!nodeFocus.disabled){var fieldFocus=basicForm.findField(nodeFocus.name);if(fieldFocus){fieldFocus.focus()}}else{var fieldInvalid=null;basicForm.items.each(function(f){if(!f.validate()){if(!f.name||!f.getErrors().length){if(f.items&&f.items.each){f.items.each(function(invalidF){if(!invalidF.validate()){fieldInvalid=invalidF;return!1}});if(fieldInvalid){return!1}}}
fieldInvalid=f}});if(fieldInvalid){fieldInvalid.focus()}else{this.callSave()}}},closeWinSubmit:function(reloadStoreAppMain){var grids=this.findByType(Ext.grid.GridPanel),sm;grids.each(function(grid,indexGrid){if(grid.isVisible()){sm=grid.getSelectionModel();if(sm.clearSelections){sm.clearSelections()}}});if(this.closeAction=='hide'){this.hide();return!1}
this.isClosedWinSubmit=!0;this.close();if(this.reloadStoreAppMainActionAll&&reloadStoreAppMain!==!1){reloadStoreAppMain=!0}
if(reloadStoreAppMain){Exj.appMainReloadStore(this)}
return!0},resetWinSubmit:function(){var bf=this._formW.getForm();bf.items.each(function(f){if(f.clearValue&&Ext.isFunction(f.clearValue)){f.clearValue()}
if(f.setValue&&Ext.isFunction(f.setValue)){f.originalValue=''}
f.clearInvalid()});bf.reset();if(this.fnSuccessActionReset){this.fnSuccessActionReset(bf,this._formW,this)}},_initWin:function(cfgFormPanel){var me=this;if(this.fnClose){this.addListener('close',function(panel){this.fnClose(this,'close')});this.addListener('hide',function(sender){this.fnClose(this,'hide')})}
if(this.maximizable){this.addListener('maximize',function(sender){sender.doLayout();if(this.fnResize){this.fnResize(sender,!0,'maximize')}});this.addListener('restore',function(sender){sender.doLayout();if(this.fnResize){this.fnResize(sender,!1,'restore')}})}
if(this._btnOk){this._btnOk.addListener('click',function(senderBtn,e){if(!me.isValid()){return}
me.doSubmit()})}
this.addListener('show',function(){var km=this.getKeyMap();km.on(13,this.onEnterTab,this)});cfgFormPanel=Ext.apply({title:'',border:!1,labelWidth:33,bodyStyle:Exj.Panel.bodyStyle,clientValidation:!0,layoutConfig:{labelSeparator:':'},defaults:{msgTarget:'qtip'},autoWidth:!0,autoHeight:!0,defaultType:'textfield'},cfgFormPanel);this._formW=new Ext.form.FormPanel(cfgFormPanel);this.add(this._formW)},getBasicForm:function(){return this._formW.getForm()},getFormPanelMain:function(){return this._formW},setRawValueFieldsMoney:function(fields,rec){if(!fields||!fields.length||!rec||!rec.data){return}
var bf=this.getBasicForm();for(var i=0,nf,nuf,val;i<fields.length;i++){nf=fields[i];nuf=bf.findField(nf);if(!nuf||!nuf.setRawValue){Exj.mou('No existe Campo: '+nf,'ERROR setRawValueFieldsMoney');continue}
val=rec.data[nf];if(val===undefined||Ext.num(val,null)===null){continue}
nuf.setRawValue(Exj.rendererRound(val))}},findGridByChildKey:function(childKey){var gridChild=null;if(!childKey){return gridChild}
var grids=this.findByType('grid');for(var i=0;i<grids.length;i++){if(grids[i].childKey==childKey){gridChild=grids[i];break}}
return gridChild},isValid:function(canShowMsg){var f=this.getBasicForm();if(this.isReadOnlyAccessSubmit){if(canShowMsg===!1){Exj.moi('No está permitido guardar, acceso de solo lectura.')}
return!1}
if(this.fnIsValidBefore){if(this.fnIsValidBefore(f,this._formW)===!1){return!1}}
if(!f.isValid()){if(canShowMsg||canShowMsg===undefined){Exj.mou('Existen errores en el formulario.<br />Por favor revizar',this.title)}
if(this.fnIsValidAfter){this.fnIsValidAfter(!1,f,this._formW)}
return!1}
if(this.fnIsValidAfter){if(this.fnIsValidAfter(!0,f,this._formW)===!1){return!1}}
if(this.fnIsValid){return this.fnIsValid.call(this,f,this._formW)}
return!0},_applyAutoAdjSizeComp:function(compAdjustSize){this.addListener('beforeshow',function(senderWin){if(!senderWin._addedListenersPanelsForLayout){var pnlsInners=this.findByType(Ext.Panel);for(var i=0,pnlInner=null;i<pnlsInners.length;i++){pnlInner=pnlsInners[i];pnlInner.addListener('collapse',function(senderPnlx){senderWin.doLayout()});pnlInner.addListener('expand',function(senderPnlx){senderWin.doLayout()})}
senderWin._addedListenersPanelsForLayout=!0}});this.addListener('afterlayout',function(senderCont,layout){var heightOffset=compAdjustSize.exjAdjustSize.heightOffset;if(heightOffset==undefined){heightOffset=0}
var heightComps=0;for(var i=0,itemComp=null;i<compAdjustSize.exjAdjustSize.items.length;i++){itemComp=compAdjustSize.exjAdjustSize.items[i];if(!itemComp.getResizeEl()){break}
heightOffset-=2;heightComps+=itemComp.getHeight()}
if(!heightComps){return}
var hTotal=this.getInnerHeight();var hToFix=hTotal-heightComps+heightOffset;if(hToFix<135){hToFix=135}
compAdjustSize.setHeight(hToFix)})},addToForm:function(obj){if(!obj){return}
if(obj.exjAdjustSize){this._applyAutoAdjSizeComp(obj)}
this._formW.add(obj)},getFieldFromName:function(nameField){return Exj.getFieldFromName(this._formW,nameField)},clearInvalid:function(){return this.getBasicForm().clearInvalid()},isDirty:function(){return this.getBasicForm().isDirty()},getValues:function(){return this.getBasicForm().getValues()},loadRecord:function(r){return this.getBasicForm().loadRecord(r)},reset:function(){this.forceDirtyOnlyOnReadFields(!1);return this.getBasicForm().reset()},getURLToSubmit:function(){if(this.onlyModeLocal){return''}
if(this.urlSubmit){return Exj.addParamsURL(this.urlSubmit)}
if(this.hUrl){if(!(this.hUrl instanceof Exj.HUrl)){alert('Se ha enviado como parámetro hUrl, pero no es una instancia de la clase: Exj.HUrl');return null}
this.setId(this.idValue);if(this.isNew){return this.hUrl.getActionCreate()}else{return this.hUrl.getActionUpdate()}}
return null},setId:function(id){if(this.hUrl&&this.hUrl.setId){this.hUrl.setId(id)}
this.isNew=!(id&&(id!='0'));if(Ext.isNumber(id)&&id<0){this.isNew=!0}
if(this.idValue!=id){this.idValue=id}},isNewData:function(){if(this.isNew===undefined&&Ext.isNumber(this.idValue)&&this.idValue>0){return!1}
return this.isNew},getFieldValues:function(dirtyOnly,onlyEnabled){var o={},n,key,val,nProps=0,isDirtyField;if(onlyEnabled===undefined){onlyEnabled=!1}
var bf=this.getBasicForm();var _fnReadField=function(f){if(onlyEnabled&&f.disabled){return!0}
if(f.isFormField){if(f.isComposite){f.items.each(_fnReadField);return!0}}
val=Exj.getValueFromCmp(f);isDirtyField=String(val)!==String(f.originalValue);if(dirtyOnly!==!0||isDirtyField){if(f.getName){n=f.getName()}else{n=f.name}
if(!n){n=f.id}
key=o[n];if(Ext.isDefined(key)){if(Ext.isArray(key)){o[n].push(val)}else{o[n]=[key,val]}}else{o[n]=val;++nProps}}};bf.items.each(_fnReadField);if(this.fnGetFieldsExtras){var fieldsExtras=this.fnGetFieldsExtras();for(var i=0,fieldExtra;i<fieldsExtras.length;i++){fieldExtra=fieldsExtras[i];_fnReadField(fieldExtra)}}
var childsList=this._formW.findByType(Ext.grid.GridPanel);if(childsList&&childsList.length>0){var dataChilds=[];for(var i=0,gridChild;i<childsList.length;i++){gridChild=childsList[i];if(!gridChild.childEditable){continue}
var dataChangeChild=Exj.getDataChangesFromStore(gridChild.getStore());dataChangeChild.haveChanges=(dataChangeChild.haveChanges?1:0);if(dataChangeChild.haveChanges){dataChilds.push({childKey:gridChild.childKey,option:gridChild.childOption,nameList:gridChild.childList,nameEditable:gridChild.childEditable,parentEditable:gridChild.parentEditable,data:dataChangeChild});if(dataChangeChild.news&&dataChangeChild.news.length){nProps+=dataChangeChild.news.length}
if(dataChangeChild.edited&&dataChangeChild.edited.length){nProps+=dataChangeChild.edited.length}
if(dataChangeChild.idsDeleted&&dataChangeChild.idsDeleted.length){nProps+=dataChangeChild.idsDeleted.length}}}
if(dataChilds.length>0){if(Ext.isDefined(o._dataChilds)){Exj.moe('Un componente tiene el nombre: _dataChilds.<br/>Este es un nombre revervado, se tiene que deninir otro nombre','ERROR EN DEFINICION DE CAMPOS')}else{o._dataChilds=dataChilds}}}
if(nProps<=0){return null}
return o},getFieldAllValues:function(dirtyOnly){var fieldAllValues={},n,key,val,nProps=0,isDirtyField,rCombo;var _fnReadAllField=function(f){val=Exj.getValueFromCmp(f);isDirtyField=String(val)!==String(f.originalValue);if(dirtyOnly===!0&&!isDirtyField){return!0}
if(f.getName){n=f.getName()}else{n=f.name}
if(!n){n=f.id}
if(f instanceof Ext.form.ComboBox){rCombo=f.findRecord(f.valueField,val);if(rCombo){for(nameProp in rCombo.data){if(nameProp=='value'||nameProp=='text'){continue}
fieldAllValues[nameProp]=rCombo.data[nameProp];++nProps}}}
key=fieldAllValues[n];if(Ext.isDefined(key)){if(Ext.isArray(key)){fieldAllValues[n].push(val)}else{fieldAllValues[n]=val}}else{fieldAllValues[n]=val;++nProps}};this.getBasicForm().items.each(_fnReadAllField);if(nProps<=0){return null}
return fieldAllValues},getAllFields:function(dirtyOnly){var allFields=[],val,nProps=0,isDirtyField;var _fnReadAllField=function(f){val=Exj.getValueFromCmp(f);isDirtyField=String(val)!==String(f.originalValue);if(dirtyOnly===!0&&!isDirtyField){return!0}
allFields.push(f);++nProps};this.getBasicForm().items.each(_fnReadAllField);if(nProps<=0){return null}
return allFields},getMethodToSubmit:function(){if(this.methodToSubmit){return this.methodToSubmit}
if(!this.isNew){return'PUT'}
return'POST'},forceDirtyOnlyOnReadFields:function(isForce){if(isForce===undefined){isForce=!0}
this._forceDirtyOnlyOnReadFields=(isForce?!0:!1)},doSubmit:function(options){var me=this;if(!options){options={}}
if(me.isReadOnlyAccessSubmit){Exj.moi('No está permitido guardar es solo de lectura.');return!1}
var dirtyOnly=!me.isNew;if(me._forceDirtyOnlyOnReadFields){dirtyOnly=!0}
var dataChanged=this.getFieldValues(dirtyOnly,me.onlyEnabledDataChange);if(me.fnGetDataChangesExtras&&Ext.isFunction(me.fnGetDataChangesExtras)){var dataChangesExtras=me.fnGetDataChangesExtras(dataChanged);if(dataChangesExtras&&Ext.isObject(dataChangesExtras)){if(!dataChanged){dataChanged={}}
dataChanged=Ext.apply(dataChangesExtras,dataChanged)}}
if(!dataChanged){Exj.moi('No ha realizado ningún cambio!',this.title);return!1}
var bf=this.getBasicForm();if(this.fnBeforeSubmit){if(this.fnBeforeSubmit(dataChanged)===!1){return!1}
if(!bf.isValid()){Exj.moe('Existen errores en el formulario.<br/>Por favor revizar...',me.title);return!1}}
options=Ext.apply({clientValidation:!0,url:me.getURLToSubmit(),params:{}},options);if(!options.url&&!me.onlyModeLocal){Exj.moe('No se ha indicado la url!',this.title);return!1}
if(me.params&&Ext.isObject(me.params)){Ext.apply(options.params,me.params)}
options.params.data=Ext.apply({isNew:(me.isNew?1:0),id:parseInt(me.idValue)},options.params.data);if(me.isRestFul!==undefined){options.params.isRestFul=me.isRestFul}
if(this.fnGetParamsData){var paramsData=this.fnGetParamsData(this,bf);if(paramsData&&Ext.isArray(paramsData)&&paramsData.length){for(var i=0,paramData;i<paramsData.length;i++){paramData=paramsData[i];options.params.data=Ext.apply(paramData,options.params.data)}}}
options.params.data=Ext.encode(options.params.data);options.params.dataChanged=Ext.encode(dataChanged);if(options.params.dataChanged=='{}'){Exj.moi('No hay cambios hechos!',this.title);return!1}
if(me._formW.fileUpload){options.params.isRestFul=!1;if(options.timeOutSec===undefined){options.timeOutSec=60}}
if(me._timeOutSec&&!options.timeOutSec){options.timeOutSec=me._timeOutSec}
if(options.timeOutSec){bf.timeout=options.timeOutSec}
if(me.onlyModeLocal){if(me.fnClientSuccess){var dataAllChanged=me.getFieldAllValues(!me.isNew);if(me.fnClientSuccess.call(me,bf,dataAllChanged,dataChanged)===!1){return}}
me.closeWinSubmit();return!0}
me.isSaving=!0;return bf.submit({clientValidation:options.clientValidation,method:me.getMethodToSubmit(),url:options.url,waitTitle:'Por favor espere...',waitMsg:me.waitMsg,params:options.params,success:function(form,action){me.isSaving=!1;if(!Exj.isSuccessResponse(action.result)){if(me.fnFailure){me.fnFailure(form,action.result)}
return}
if(me.isSuccessActionReset){me.resetWinSubmit()}else if(!me.isSuccessActionNone){me.closeWinSubmit()}
if(me.fnSuccess){me.isSaving=!0;setTimeout(function(){me.isSaving=!1;me.fnSuccess(form,action.result,action);if(options.fnCallbackSuccess){options.fnCallbackSuccess(form,action.result,action)}},300)}else if(options.fnCallbackSuccess){options.fnCallbackSuccess(form,action.result,action)}},failure:function(form,action){me.isSaving=!1;Exj.showMsgFailure(action);if(me.fnFailure){me.fnFailure(form,action.result)}}})},calcHeight:function(percent){if(percent==undefined){percent=100}
var _h=this.height;if(this.isVisible()){_h=this.getInnerHeight()}
return Exj.round((_h*(percent/100)),3)},calcWidth:function(percent){if(percent==undefined){percent=100}
var _w=this.width;if(this.isVisible()){_w=this.getInnerWidth()}
return Exj.round((_w*(percent/100)),3)},addButtonToolBar:function(btn){return this.getTopToolbar().add(btn)},setDisabledCancel:function(pDisabled){if(!this._btnCancel){return!1}
if(pDisabled===undefined){pDisabled=!0}
return this._btnCancel.setDisabled(pDisabled)},bindToContainer:function(record,fieldFocus){if(fieldFocus==undefined){fieldFocus=''}
return Exj.bindToContainer(this,record,fieldFocus,!0)},callSave:function(){if(!this._btnOk||this._btnOk.disabled){return!1}
this._btnOk.fireEvent('click',this._btnOk);return!0}});Exj.showMsgFailure=function(action){if(!action){return}
switch(action.failureType){case Ext.form.Action.CLIENT_INVALID:Exj.moe('Existen errores, por favor revizar');break;case Ext.form.Action.CONNECT_FAILURE:var msgErrorFailure='';if(action.response){if(action.response.responseText){if(Exj.isValidJSON(action.response.responseText)){var objResponse=Ext.decode(action.response.responseText);if(objResponse.Msg&&objResponse.Msg.text){Exj.isSuccessResponse(objResponse);return}}}
if(!msgErrorFailure&&action.response.statusText){msgErrorFailure=action.response.statusText}}
if(!msgErrorFailure){msgErrorFailure='Conexión fallida, pruebe otra vez'}
Exj.moe(msgErrorFailure);break;case Ext.form.Action.SERVER_INVALID:Exj.isSuccessResponse(action.result);break;default:Exj.moe('Error 3000. Desconocido')}};Exj.getDataChangeFromModelEditable=function(dataChangedAll,editableModel){var newDataChange=null;if(!dataChangedAll||!editableModel.uie||!editableModel.uie.items){return newDataChange}
Ext.each(editableModel.uie.items,function(item){var nameField=item.name;var value=dataChangedAll[nameField];if(value!==undefined){if(!newDataChange){newDataChange=new Object()}
newDataChange[nameField]=value}});return newDataChange};Exj.getNamesUsr=function(){return(Exj.Global.infoUser.nombres_persona+' '+Exj.Global.infoUser.apellidos_persona)};Exj.applyFieldKey=function(pnl,nameField){pnl._fieldKey=new Ext.form.Hidden({name:nameField,xtype:'hidden'});pnl.add(pnl._fieldKey);pnl.setValueId=function(valueId){pnl._fieldKey.setValue(valueId)};pnl.getValueId=function(isInt){if(isInt===undefined){isInt=!0}
var valueId=pnl._fieldKey.getValue();if(!isInt){return valueId}
if(!valueId){valueId=0}
return parseInt(valueId)}};Exj.getValueFromCmp=function(cmp){if(!cmp){return null}
if(!cmp.getValue){return null}
var val=cmp.getValue();if(val){if(val instanceof Ext.form.Checkbox){if(val.inputValue===undefined){val=Exj.getValueFromCmp(val)}else{val=val.inputValue}}}
return val};Exj.getComponent=function(container,valueCmp){if(!container||!valueCmp){return null}
if(!container.getComponent){return null}
var cmpFound=container.getComponent(valueCmp);if(!cmpFound&&container.items&&container.items.each){var findCmpCustom=function(cmp){if(cmp.dataIndex==valueCmp||cmp.id==valueCmp||(cmp.isFormField&&cmp.name==valueCmp)){cmpFound=cmp;return!1}else if(cmp.isFormField){if(cmp.isComposite){return cmp.items.each(findCmpCustom)}else if(cmp instanceof Ext.form.CheckboxGroup&&cmp.rendered){return cmp.eachItem(findCmpCustom)}}else if(cmp.items&&cmp.items.each){return cmp.items.each(findCmpCustom)}};container.items.each(findCmpCustom)}
return cmpFound};Exj.getFieldsFromContainer=function(container){var fields=[];if(container.findByType){fields=container.findByType(Ext.form.Field)}
return fields};Exj.resetFieldsOfContainer=function(container){var fields=Exj.getFieldsFromContainer(container);for(var i=0,f;i<fields.length;i++){f=fields[i];if(f.reset){f.reset()}}};Exj.getFieldFromName=function(container,value){if(!container||!value){return null}
if(!container.find){return null}
var cmps=container.find('name',value);if(!cmps||cmps.length==0){var fieldFound=null;if(container.getBasicForm&&Ext.isFunction(container.getBasicForm)){fieldFound=container.getBasicForm().findField(value)}else if(container.items){var findFieldCustom=function(f){if(f.isFormField){if(f.dataIndex==value||f.id==value||f.getName()==value){fieldFound=f;return!1}else if(f.isComposite){return f.items.each(findFieldCustom)}else if(f instanceof Ext.form.CheckboxGroup&&f.rendered){return f.eachItem(findFieldCustom)}}else if(f.items&&f.items.each){return f.items.each(findFieldCustom)}};container.items.each(findFieldCustom)}
return fieldFound}
return cmps[0]};Exj.gridInsertRecord=function(grid,dataRecord,fieldKey,colIndexEditing,useMethodAdd,rowIndexEditing){dataRecord=dataRecord||{};dataRecord.modificado_dt=new Date();dataRecord.name_usr=Exj.getNamesUsr();if(!Ext.isDefined(grid.store._lastAutoId)){grid.store._lastAutoId=0}
grid.store._lastAutoId-=1;if(fieldKey){dataRecord[fieldKey]=grid.store._lastAutoId}
var p=new grid.store.recordType(dataRecord,grid.store._lastAutoId);if(grid.stopEditing){grid.stopEditing()}
p.phantom=!0;p.markDirty();if(rowIndexEditing===undefined){rowIndexEditing='auto'}
if(useMethodAdd){grid.store.add(p);if(rowIndexEditing!='auto'){rowIndexEditing=grid.store.data.length-1;if(rowIndexEditing<0){rowIndexEditing=0}}}else{grid.store.insert(0,p)}
if(grid.startEditing&&colIndexEditing!==undefined){if(rowIndexEditing=='auto'){rowIndexEditing=0;if(grid.store.data.length>1){var field=grid.colModel.getColumnAt(colIndexEditing).dataIndex;for(var rowText=0;rowText<grid.store.data.length;rowText++){rowIndexEditing=rowText;var r=grid.store.getAt(rowIndexEditing);if(!r.data[field]){break}}}}
if(colIndexEditing=='auto'){colIndexEditing=0}
grid.startEditing(rowIndexEditing,colIndexEditing)}
return p};Exj.getContainerGridsCenter=function(items){return{xtype:'container',layout:'exj_gridcenter',baseCls:'x-plain',items:items}};Exj.newGridPanelFromListModel=function(uiList,urlView,baseParamsExtras,onlyModeLocal){uiList=Ext.apply({cfgStore:{},cfgGrid:{bbar:{}},cfgSelModel:{type:'RowSelectionModel',params:{singleSelect:!0}},nameClassGrid:''},uiList);if(uiList.isModeLocal){urlView=''}
if(uiList.isModeLocal||onlyModeLocal){if(uiList.cfgStore.pruneModifiedRecords===undefined){uiList.cfgStore.pruneModifiedRecords=!0}}
var store=Exj.newJsonStore(uiList.cfgStore,urlView,baseParamsExtras,onlyModeLocal);if(uiList.sortField){if(!uiList.sortDir){uiList.sortDir='ASC'}
store.setDefaultSort(uiList.sortField,uiList.sortDir)}
uiList.cfgGrid.store=store;uiList.cfgGrid.bbar.store=store;if(uiList.useLockingGrid){uiList.cfgGrid.view=new Ext.ux.grid.LockingGridView();uiList.cfgGrid.colModel=new Ext.ux.grid.LockingColumnModel(Exj.cloneSmart(uiList.cfgGrid.columns));uiList.cfgSelModel.type=null;delete uiList.cfgGrid.columns;uiList.cfgGrid.stripeRows=!0}
if(uiList.cfgSelModel.type&&!uiList.cfgGrid.sm){switch(uiList.cfgSelModel.type){case 'RowSelectionModel':uiList.cfgGrid.sm=new Ext.grid.RowSelectionModel(uiList.cfgSelModel.params);break;case 'CheckboxSelectionModel':uiList.cfgGrid.sm=new Ext.grid.CheckboxSelectionModel(uiList.cfgSelModel.params);var colFirst=uiList.cfgGrid.columns[0];if(!(colFirst instanceof Ext.grid.CheckboxSelectionModel)){var cols=new Array();cols.push(uiList.cfgGrid.sm);for(var i=0;i<uiList.cfgGrid.columns.length;i++){cols.push(uiList.cfgGrid.columns[i])}
uiList.cfgGrid.columns=cols}
break;case 'CellSelectionModel':uiList.cfgGrid.sm=new Ext.grid.CellSelectionModel(uiList.cfgSelModel.params);break;default:Exj.moe('El tipo: '+uiList.cfgSelModel.type+', no esta soportado en la UI','Error de implementación de Modelo Listas');break}}
if(uiList.cfgGrid.columns&&uiList.canCloneColumns){if(uiList.cfgGrid._lastColumnsCloned){uiList.cfgGrid.columns=Exj.cloneSmart(uiList.cfgGrid._lastColumnsCloned)}else{uiList.cfgGrid._lastColumnsCloned=Exj.cloneSmart(uiList.cfgGrid.columns)}}
if(!uiList.nameClassGrid){uiList.nameClassGrid='GridPanel'}
var evalExpGrid='';if(uiList.nameClassGrid!='GridPanel'&&uiList.nameClassGrid.indexOf('.')>=1){evalExpGrid=uiList.nameClassGrid}else{evalExpGrid='Ext.grid.'+uiList.nameClassGrid}
var ClassGrid=eval(evalExpGrid);var grid=new ClassGrid(uiList.cfgGrid);if(uiList.data){if(grid.store.root){grid.store.loadData(uiList.data)}else{grid.store.loadData(uiList.data.DataTopics.topics)}}
grid.isModeLocal=uiList.isModeLocal;grid.isModeRemote=uiList.isModeRemote;if(onlyModeLocal){grid.isModeLocal=!0;grid.isModeRemote=!1}
if(grid.isModeLocal){grid.getBottomToolbar().setVisible(!1)}
Exj.action.grid.onNew(grid,function(senderButton,e){if(grid.onActionNew){grid.onActionNew(senderButton,e)}});Exj.action.grid.onEdit(grid,function(senderButton,e,r){if(grid.onActionEdit){grid.onActionEdit(senderButton,e,r)}});Exj.action.grid.onDel(grid,function(senderButton,e,r){if(grid.onActionDel){grid.onActionDel(senderButton,e,r);return}
if(grid.isModeLocal){grid.getStore().remove(r)}});return grid};Exj.newPorcNumberField=function(config){if(!config){config=new Object()}
if(config.blankText){config.allowBlank=!1}
if(config.allowNegative==undefined){config.allowNegative=!1}
if(config.decimalPrecision==undefined){config.decimalPrecision=4}
if(config.maxText==undefined){config.maxText=9}
if(config.width==undefined){config.width=60}
var nfPorc=new Ext.form.NumberField(config);return nfPorc};Exj.newNumberField=function(config){if(!config){config=new Object()}
if(config.blankText){config.allowBlank=!1}
if(config.allowNegative==undefined){config.allowNegative=!1}
if(config.decimalPrecision==undefined){config.decimalPrecision=4}
if(config.maxText==undefined){config.maxText=9}
if(config.width==undefined){config.width=60}
return new Ext.form.NumberField(config)};Exj.PanelGenerarCodigo=function(config){var cmdGenerar;var pGenCode;var txtCodigo;cmdGenerar=Exj.newButton({text:'...',tooltip:'Genera un código único'});txtCodigo=Exj.newTextField({fieldLabel:'Código',allowBlank:!1,blankText:'Código es requerido',width:'84%'});pGenCode=new Ext.Panel({title:'',bodyStyle:Exj.Panel.bodyStyle,autoHeight:!0,border:!1,layout:'column',items:[{columnWidth:0.70,autoHeight:!0,border:!1,xtype:'fieldset',labelWidth:42,items:[txtCodigo]},{columnWidth:0.30,autoHeight:!0,border:!1,items:[cmdGenerar]}]});function setHandler(fn){cmdGenerar.setHandler(fn)};function submit(params){Exj.submit({url:params.url,params:params.params,idMask:txtCodigo.getEl(),mask:'Por favor espere...',fnSuccess:function(r){txtCodigo.setValue(r.data);if(params.success){params.success(r.data)}}})};return{getPanel:function(){return pGenCode},getValueCode:function(){return txtCodigo.getValue()},setValueCode:function(value){return txtCodigo.setValue(value)},setHandler:setHandler,submit:submit}};Exj.newDateFieldDateTime=function(config){if(!config){config=new Object()}
if(config.fieldLabel==undefined){config.fieldLabel='Date'}
config.fieldLabel=Exj.Idioma(config.fieldLabel);if(config.allowBlank==undefined){config.allowBlank=!1}
if(config.blankText==undefined){config.blankText='The: '+config.fieldLabel+' required'}
if(config.minLength==undefined){config.minLength=10}
if(config.minLengthText==undefined){config.minLengthText='The minimum date of 10 characters'}
if(config.maxLength==undefined){config.maxLength=19}
if(config.maxLengthText==undefined){config.maxLengthText='The maximum longuitud to date is 19 characters'}
if(config.format==undefined){config.format=Exj.FormatDateTime}
if(config.invalidText==undefined){config.invalidText='It is not a valid date - it must be in the format {day-month-year Hour:minutes}'}
if(config.width==undefined){config.width=120}
return(new Ext.form.DateField(config))};Exj.newDateFieldDate=function(config){config=config||{};config=Ext.apply({fieldLabel:'Date',allowBlank:!1,minLength:8,minLengthText:'The minimum date of 8 characters',blankText:'This field is required',maxLength:8,maxLengthText:'The maximum longuitud to date is 8 characters',format:Exj.FormatDate,invalidText:'It is not a valid date - it must be in the format {day-month-year}',width:81},config);config.fieldLabel=Exj.Idioma(config.fieldLabel);var _dt=new Ext.form.DateField(config);_dt.getValueToServer=function(){return Exj.getDateToServer(_dt.getValue())};return _dt};Exj.getDateCurrent=function(clearTime){var today=new Date();if(clearTime||clearTime===undefined){today.clearTime()}
return today};Exj.execActionEditViewFromNode=function(scope){var maxRec=30;function _findGrid(nodeX){if(!nodeX){return null}
maxRec-=1;if(maxRec<0){return null}
if(nodeX.id){var comp=Ext.getCmp(nodeX.id);if(comp&&(comp instanceof Ext.grid.GridPanel)){return comp}}
if(!nodeX.parentNode){return null}
return _findGrid(nodeX.parentNode)};var grid=_findGrid(scope.parentNode);if(!grid){Exj.moe('No se encontró grid.');return}
var buttons=grid.getTopToolbar().find('exjAction','edit');if(!buttons||!buttons.length){buttons=grid.getTopToolbar().find('exjAction','view');if(!buttons||!buttons.length){Exj.moi('No se permite esta acción!');return}}
var btnToCall=buttons[0];btnToCall.fireEvent('click',btnToCall)};Exj.renderCSSText=function(valueText,p,params){if(!params||params.css===undefined){return valueText}
if(params.canResetCSS){p.css=''}
if(p.css){p.css+=' '}
p.css+=params.css;if(!valueText){return valueText}
if(params.converToButton){var htmlBtn='';htmlBtn+='<a class="exj-btn-item" href="#" unselectable="on" hidefocus="true" onclick="Exj.execActionEditViewFromNode(this);">';htmlBtn+='<span class="x-menu-item-text '+params.css+'">'+valueText+'</span>';htmlBtn+='</a>';return htmlBtn}
return'<span class="'+params.css+'">'+valueText+'</span>'};Exj.renderCSSDate=function(valueDate,p,params){if(!valueDate){if(params.valueDateReturn){valueDate=params.valueDateReturn}
if(valueDate&&Ext.isDate(valueDate)){valueDate=Exj.rendererFormatDate(valueDate)}
return valueDate}
var dateCompare;if(params.dateCompare){dateCompare=Exj.convertDateRawToDate(params.dateCompare)}else{dateCompare=Exj.getDateCurrent(!0)}
var dateJs=Exj.convertDateRawToDate(valueDate,!0);var dateOrJs=null;if(params.valueDateOr){dateOrJs=Exj.convertDateRawToDate(params.valueDateOr,!0)}
if(params.canResetCSS){p.css=''}
if(params.compareIsMinor&&(dateJs<dateCompare)||(dateOrJs&&dateOrJs<dateCompare)){if(p.css){p.css+=' '}
if(!params.cssDateIsMinor){params.cssDateIsMinor='exj-rep-cell-date-minor'}
p.css+=params.cssDateIsMinor}else if(params.compareIsEqual&&(dateJs.between(dateCompare,dateCompare))||(dateOrJs&&dateOrJs.between(dateCompare,dateCompare))){if(p.css){p.css+=' '}
if(!params.cssDateIsEqual){params.cssDateIsEqual='exj-rep-cell-date-current'}
p.css+=params.cssDateIsEqual}else if(params.compareIsMinor&&params.compareIsEqual&&params.cssDateIsMayor){if(p.css){p.css+=' '}
p.css+=params.cssDateIsMayor}
if(params.valueDateReturn){valueDate=params.valueDateReturn}
if(Ext.isDate(valueDate)){valueDate=Exj.rendererFormatDate(valueDate)}
if(!p.css){return valueDate}
return'<span class="'+p.css+'">'+valueDate+'</span>'};Exj.getCSSRowDate=function(valueDate,params){if(!valueDate){return valueDate}
params=Ext.apply({css:'',dateCompare:null,offsetDaysIsMayor:0,compareIsMinor:!0,compareIsMayor:!0,compareIsEqual:!1},params);if(params.compareIsEqual&&params.cssDateIsEqual===undefined){params.cssDateIsEqual='exj-rep-row-date-equal'}
var dateCompare;if(params.dateCompare){dateCompare=Exj.convertDateRawToDate(params.dateCompare)}else{dateCompare=Exj.getDateCurrent(!0)}
var dateJs=Exj.convertDateRawToDate(valueDate,!0);if(params.compareIsMinor&&dateJs<dateCompare){if(params.css){params.css+=' '}
if(!params.cssDateIsMinor){params.cssDateIsMinor='exj-rep-row-date-minor'}
params.css+=params.cssDateIsMinor}else if(params.compareIsMayor){if(Ext.isNumber(params.offsetDaysIsMayor)&&params.offsetDaysIsMayor!=0){dateCompare=dateCompare.add(Date.DAY,params.offsetDaysIsMayor)}
if(dateJs>dateCompare||((Ext.isNumber(params.offsetDaysIsMayor)&&params.offsetDaysIsMayor!=0)&&dateJs.between(dateCompare,dateCompare))){if(params.css){params.css+=' '}
if(!params.cssDateIsMayor){params.cssDateIsMayor='exj-rep-row-date-mayor'}
params.css+=params.cssDateIsMayor}}else if(params.compareIsEqual&&dateJs.between(dateCompare,dateCompare)){if(params.css){params.css+=' '}
if(!params.cssDateIsEqual){params.cssDateIsEqual='exj-rep-row-date-default'}
params.css+=params.cssDateIsEqual}else{if(params.css){params.css+=' '}
params.css+='exj-rep-row-date-default'}
return params.css};Exj.getDateTimeFromServer=function(dateTime){var dt=Date.parseDate(dateTime,Exj.dateTimeFormat);if(!dt){return dateTime}
return dt.format(Exj.FormatDateTime)};Exj.getDateTimeFromServerForJs=function(dateTime){var dt=Date.parseDate(dateTime,Exj.dateTimeFormat);if(!dt){return dateTime}
return dt};Exj.parseDateFormats=function(strDate,formats){if(!strDate||Ext.isDate(strDate)){return strDate}
if(!formats){formats=[Exj.FormatDate,Exj.dateFormat,Exj.FormatDateTime,Exj.dateTimeFormat,'Y-m-dTH:i:s']}
var dateJs=null;if(Ext.isArray(formats)){for(var i=0,f;i<formats.length;i++){f=formats[i];dateJs=Date.parseDate(strDate,f);if(dateJs){break}}}else{dateJs=Date.parseDate(strDate,formats)}
return dateJs};Exj.convertDateRawToDate=function(dateX,clearTime){var dateJs=Exj.parseDateFormats(dateX);if(!dateJs){alert('ERROR. NO SE PUDO CONVERTIR FECHA RAW A FECHAJS. FECHA: '+dateX);return dateX}
if(clearTime||(clearTime===undefined)){dateJs.clearTime()}
return dateJs};Exj.getDateFromServer=function(dateX){var dt=Date.parseDate(dateX,Exj.dateFormat);if(!dt){return dateX}
return dt.format(Exj.FormatDate)};Exj.getDateTimeToServer=function(dt){if(!dt){return''}
return dt.format(Exj.dateTimeFormat)};Exj.getDateToServer=function(dt,transformToDate){if(!dt){return''}
if(transformToDate==undefined){transformToDate=!1}
if(transformToDate){dt=Date.parseDate(dt,Exj.FormatDate)}
return dt.format(Exj.dateFormat)};Exj.isEqualDates=function(date1,date2){if(date1===date2){return!0}
if(!date1||!date2){return!1}
if(date1.format(Exj.dateFormat)==date2.format(Exj.dateFormat)){return!0}
return!1};Exj.getDateFirstDateOfMonth=function(){var _date=new Date();return _date.getFirstDateOfMonth()};Exj.newTextFieldTime=function(config){if(config.fieldLabel==undefined){config.fieldLabel='Hour'}
if(config.allowBlank==undefined){config.allowBlank=!1}
if(config.blankText==undefined){config.blankText='This value is required'}
if(config.width==undefined){config.width=66}
var tf=Exj.newTextField(config);tf.isValidTime=function(){if(tf.getValue().length<8){Exj.mou('La longitud mínima para {'+config.fieldLabel+'} es de 8 caracteres');return!1}
if(tf.getValue().length>8){Exj.mou('La longitud máxima para {'+config.fieldLabel+'} es de 8 caracteres');return!1}
var dt=Date.parseDate(tf.getValue(),'H:i:s');if(!dt){Exj.mou(config.fieldLabel+' es una hora no válida, debe ingresar en el formato: {hora:minutos:segundos}');return!1}
tf.setValue(dt.format('H:i:s'));return!0};return tf};Exj.setDataComboArray=function(objComboBox,dataCombo,isValueInt){if(isValueInt===undefined){isValueInt=!1}
var d=new Array();var index=-1;var itemCombo;if(dataCombo){while(++index<dataCombo.length){itemCombo=dataCombo[index];if(isValueInt){itemCombo.value=parseInt(itemCombo.value)}
d[index]=[itemCombo.value,itemCombo.text]}}
var fields=new Array();var _index=-1;if(isValueInt){fields[++_index]={name:'value',type:'int'};fields[++_index]={name:'text'}}else{fields[++_index]={name:'value'};fields[++_index]={name:'text'}}
var store=new Ext.data.SimpleStore({fields:fields,data:d});objComboBox.store=store};Exj.newGridFromEditableModel=function(config){config=config||{};var editableModel=config.editableModel;var nameList=config.nameList;var scopeModule=config.scopeModule;var baseParams=config.baseParams;var onlyModeLocal=config.onlyModeLocal;if(onlyModeLocal===undefined){onlyModeLocal=!1}
var showMsgValidation=config.showMsgValidation;if(showMsgValidation===undefined){showMsgValidation=!0}
config.msgValidation={msg:'',title:''};function _invalidateMsg(msg,title){config.msgValidation={msg:msg,title:(title===undefined?'ERROR en newGridFromEditableModel':title)};if(showMsgValidation){Exj.moe(config.msgValidation.msg,config.msgValidation.title)}
return null};if(!editableModel){Exj.moe('No se ha indicado el modelo editable','ERROR en newGridFromEditableModel');return null}
if(!editableModel.childsList||editableModel.childsList.length<=0){return _invalidateMsg('No se existen listas hijas del modelo editable')}
var childList=null;if(!nameList&&editableModel.childsList.length==1){childList=editableModel.childsList[0];nameList=childList.dataIndex}else{for(var i=0,item;i<editableModel.childsList.length;i++){item=editableModel.childsList[i];if(item.dataIndex==nameList){childList=item;break}}}
if(!nameList){Exj.moe('No se indicó el nombre de la lista hija del modelo editable','ERROR en newGridFromEditableModel');return null}
if(!childList){return _invalidateMsg('No se encontró lista hija del modelo editable: '+nameList)}
if(!childList.listModel){Exj.moe('En la lista hija del modelo editable: '+nameList+'<br/>No existe modelo de lista!','ERROR en newGridFromEditableModel');return null}
if(!scopeModule){Exj.moe('En la lista hija del modelo editable: '+nameList+'<br/>No se ha pasado la referencia del módulo main!','ERROR en newGridFromEditableModel');return null}
var hUrlList=new Exj.HUrl({option:'',controller:childList.nameController});Exj.evalRendererListModel(childList.listModel,scopeModule);var urlProxy='';if(childList.listModel.isModeRemote){urlProxy=hUrlList.getActionView()}
var gridListModel=Exj.newGridPanelFromListModel(childList.listModel,urlProxy,baseParams,onlyModeLocal);gridListModel.getHUrl=function(){return hUrlList};gridListModel.store.addListener('load',function(sto,records,options){if(!records||records.length==0){return}
for(var i=0,r;i<records.length;i++){r=records[i];r.phantom=!1}});var editable=null;if(childList.uiEditable){editable=new Exj.ui.Editable(childList.uiEditable)}
Exj.action.grid.onNew(gridListModel,function(senderButton,e){if(!config.onActionNew){return}
config.onActionNew(senderButton,e,hUrlList,editable,gridListModel)});Exj.action.grid.onEdit(gridListModel,function(senderButton,e,r){if(!config.onActionEdit){return}
config.onActionEdit(senderButton,e,r,hUrlList,editable,gridListModel)});Exj.action.grid.onDel(gridListModel,function(senderButton,e,r,nameFieldId){if(config.onActionDel){config.onActionDel(senderButton,e,r,hUrlList,editable,gridListModel);return!0}
config.onActionDel=function(senderButton,e,r){if(childList.listModel.isModeRemote&&r.id>0&&!onlyModeLocal){Exj.executeDelete(gridListModel,hUrlList,r,nameFieldId)}else{gridListModel.getStore().remove(r)}}
config.onActionDel(senderButton,e,r,hUrlList,editable,gridListModel)});return gridListModel};Exj.getDataChangesFromStore=function(store){var dataChanges=new Exj.DataChanges();if(store.idsDeleted&&store.idsDeleted.length>0){dataChanges.setIdsDeleted(store.idsDeleted)}
var modifiedRecords=store.getModifiedRecords();if(modifiedRecords&&modifiedRecords.length>0){for(var i=0,r;i<modifiedRecords.length;i++){r=modifiedRecords[i];if(store.baseParams){for(nameProp in store.baseParams){if(nameProp=='limit'||nameProp=='start'){continue}
if(r.data[nameProp]===undefined){r.data[nameProp]=store.baseParams[nameProp]}}}
if(r.phantom||(r.id<0)){dataChanges.addNew(r.data)}else{dataChanges.addEdited(r.data)}}}
return dataChanges.getDataChange()};Exj.executeDelete=function(grid,hUrl,r,nameFieldId,fnSuccessDel){if(!r){r=grid.getSelectionModel().getSelected()}
if(!r){return!1}
if(!r.id){Exj.moe('La lista no tiene IdProperty!','ERROR DE IMPLEMENTACION');return!1}
var id=r.id;if(!nameFieldId&&grid.exjNameFieldId){nameFieldId=grid.exjNameFieldId}
if(nameFieldId){id=r.get(nameFieldId);if(!id){Exj.moe('El campo: '+nameFieldId+' no devolvió nada del registro.','ERROR ELIMINADO REGISTRO');return!1}}
Exj.submit({method:'DELETE',mask:'Eliminando, por favor espere...',hUrl:hUrl.setId(id),fnSuccess:function(response){if(fnSuccessDel&&Ext.isFunction(fnSuccessDel)){if(fnSuccessDel(response)===!1){return}}
if(response.data&&!response.data.nDeleted){grid.store.reload();return}
grid.store.remove(r);grid.store.commitChanges()}})};Exj.addParamsURL=function(url){if(!url){return url}
var carConcat='&';var urlRet=url;if(url.indexOf('no_html=')==-1){urlRet+=carConcat+'no_html=1'}
if(url.indexOf('verApp=')==-1){urlRet+=carConcat+'verApp='+Exj.verApp}
if(Exj.Global.infoUser&&Exj.Global.infoUser.id_empresa&&url.indexOf('id_empresa=')==-1){urlRet+=carConcat+'id_empresa='+Exj.Global.infoUser.id_empresa}
return urlRet};Exj.getParamsList=function(params){var p=new Object();if(!params){params=new Object()}
if(!params.start){params.start=0}
if(!params.limit){params.limit=Exj.LIMIT}
p.start=params.start;p.limit=params.limit;return p};Exj.newWindow=function(p){p=Ext.apply({modal:!0,title:Exj.TITLE,idMask:'',autoHeight:!1,width:Exj.calcWidth(90),maximizable:!0,iconCls:'exj-icon-app',buttonAlign:'center',closeAction:'close',autoScroll:!0,plain:!0,bodyStyle:Exj.bodyStyle,buttons:[],addButtonCerrar:!0},p);if(!p.autoHeight&&!p.height){p.height=Exj.calcHeight(90)}
var win;if(p.addButtonCerrar&&p.buttons&&Ext.isArray(p.buttons)){p.buttons.push({text:Exj.Idioma('Cerrar'),iconCls:'exj-btn-cancel',tooltip:(p.title==Exj.TITLE?'Cierra la ventana actual':'Permite cerrar ventana: '+p.title),handler:function(){win.close();if(p.fnCerrar){p.fnCerrar()}}})}
win=new Ext.Window(p);if(p.autoWidth||p.width=='auto'){win.addListener('show',function(senderWin){senderWin.syncSize()})}
return win};Exj.showWinFromListModel=function(params){params=Ext.apply({listModel:null,moveTitleGridToWin:!1,win:{},urlView:'',baseParams:null,modeLocal:!0,scope:null,fnOnEdit:null,elOnShowWin:null},params);if(!params.listModel||!params.listModel.cfgGrid){alert('ERROR. NO SE INDICO listModel. REF: Exj.showWinFromListModel');return!1}
params.win=Ext.apply({modal:!0,closable:!0,maximizable:!1,autoHeight:!0,layout:'form',buttonAlign:'center'},params.win);if(params.moveTitleGridToWin){params.win.title=params.listModel.cfgGrid.title;params.listModel.cfgGrid.title=''}
Exj.evalRendererListModel(params.listModel,params.scope);var gridx=Exj.newGridPanelFromListModel(params.listModel,params.urlView,params.baseParams,params.modeLocal);var winListModel;if(params.fnOnEdit){Exj.action.grid.onEdit(gridx,function(senderButton,e,r){params.fnOnEdit(senderButton,e,r,winListModel)})}
if(!params.win.items){params.win.items=new Array()}
params.win.items.push(gridx);winListModel=Exj.newWindow(params.win);if(params.elOnShowWin){winListModel.show(params.elOnShowWin)}else{winListModel.show()}
return winListModel};Exj.uploadFile=function(params,scope){params=Ext.apply({hUrl:null,title:'',url:'',autoAddList:!1,params:{},paramDataChanged:{},senderButton:null,width:Exj.calcWidth(60),labelWidth:50,iconCls:'',fnSuccess:null,timeOutSec:60,itemsUI:null,fieldFileUpload:{},record:null},params);params.fieldFileUpload=Ext.apply({emptyText:'Seleccione un archivo haciendo clic en el icono -->',fieldLabel:'Archivo',blankText:'Seleccione el archivo haciendo clic en el próximo icono',listeners:{},buttonCfg:{text:'',iconCls:'app-btn-uploadfile'}},params.fieldFileUpload);if(!params.url&&params.hUrl){params.url=params.hUrl.getActionUploadFile()}
if(!params.url){Exj.moe('No se indicó hUrl o la url','ERROR DE IMPLEMENTACION');return!1}
var paramsSubmit=params.params;if(paramsSubmit.isRestFul===undefined){paramsSubmit.isRestFul=!1}
if(params.paramDataChanged){paramsSubmit.paramDataChanged=params.paramDataChanged}
if(!paramsSubmit.paramDataChanged){paramsSubmit.paramDataChanged=new Object()}
if(params.senderButton){if(!params.title){params.title=params.senderButton.text}
if(!params.iconCls){params.iconCls=params.senderButton.iconCls}}
if(!params.title){params.title='Cargando archivo...'}
paramsSubmit.paramDataChanged=Ext.encode(paramsSubmit.paramDataChanged);params.fieldFileUpload.xtype='fileuploadfield';params.fieldFileUpload.name='file_upload';params.fieldFileUpload.listeners.fileselected=Exj.files.onSelected;var fpArchivo=new Ext.FormPanel({fileUpload:!0,autoWidth:!0,frame:!0,autoHeight:!0,bodyStyle:'padding: 6px 6px 0 6px;',labelWidth:params.labelWidth,defaults:{anchor:'96%',allowBlank:!1,msgTarget:'qtip'},items:[params.fieldFileUpload,{xtype:'hidden',name:'id_file',value:-1}]});if(params.itemsUI){if(Ext.isArray(params.itemsUI)){for(var i=0,itemUI;i<params.itemsUI.length;i++){itemUI=params.itemsUI[i];fpArchivo.add(itemUI)}}else{fpArchivo.add(params.itemsUI)}}
var btnSave={text:'Guardar',iconCls:'app-btn-save',handler:function(){var bf=fpArchivo.getForm();if(params.timeOutSec){bf.timeout=params.timeOutSec}
if(bf.isValid()){bf.submit({url:params.url,waitTitle:'Por favor espere...',waitMsg:'Cargando archivo...',params:paramsSubmit,success:function(fp,action){if(!Exj.isSuccessResponse(action.result)){if(params.fnFailure){params.fnFailure(fp,action.result)}
return}
if(params.fnSuccess){params.fnSuccess(action.result,fp)}
winUpLoadFile.close()},failure:function(form,action){Exj.showMsgFailure(action);if(params.fnFailure){params.fnFailure(form,action.result)}}})}}};var btnCancel={text:'Cancelar',iconCls:'exj-btn-cancel',handler:function(){winUpLoadFile.close()}};var winUpLoadFile=Exj.newWindow({title:params.title,iconCls:params.iconCls,maximizable:!1,autoHeight:!0,addButtonCerrar:!1,width:params.width,items:[fpArchivo],buttons:[btnSave,btnCancel],listeners:{show:function(senderWinUpload){}}});if(params.record){if(params.fieldFocus==undefined){params.fieldFocus='file_upload'}
Exj.bindToContainer(winUpLoadFile,params.record,params.fieldFocus,!0)}
winUpLoadFile.show();return!0};Ext.autoAdjustSize=function(component,paramsAdjSize){if(!component){return}
paramsAdjSize=Ext.apply({heightOffset:0,adjustHeight:!0,items:new Array()},paramsAdjSize);component.exjAdjustSize=paramsAdjSize};Exj.getLinkHTML=function(link,text,clase,iconCls){if(!clase){clase=''}
if(!iconCls){iconCls=''}
if(iconCls){text='<img class="x-menu-item-icon '+iconCls+'" src="Lib/ext-2.2/resources/images/default/s.gif"/>'+text}
return String.format('<b><a href="'+link+'" target="_blank" class="{0}">{1}</a></b>',clase,text)};Exj.normalizeParams=function(params){if(!params||Ext.isEmpty(params)||!Ext.isObject(params)){return params}
var paramsReturn={},val,nEncode=0;for(prop in params){val=params[prop];if(val&&(Ext.isObject(val)||Ext.isArray(val))){paramsReturn[prop]=Ext.encode(val);nEncode+=1}else{paramsReturn[prop]=val}}
if(!nEncode){return params}
return paramsReturn};Exj.showEditableModel=function(params,scope){params=Ext.apply({hUrl:null,title:'',url:'',autoAddList:!1,params:{},senderButton:null,width:Exj.calcWidth(60),height:'auto',autoHeight:!0,autoScroll:!1,autoMaximizeWin:!1,items:new Array(),recordEditable:null,isReadOnlyAccess:!1,idValue:0,labelWidth:45,fnAfterShowWin:null,onlyEnabledDataChange:!1,paramsExtrasWin:null,textOk:'Guardar',renderToContainer:null,reloadStoreAppMainActionAll:!1,closable:!0,fnBeforeSubmit:null},params);if(params.autoMaximizeWin){if(params.autoScroll==undefined){params.autoScroll=!0}
if(params.height=='auto'){params.height=Exj.calcHeight(60)}
params.autoHeight=!1}
if(!params.hUrl){Exj.moe('No se indicó hUrl','Presentando modelo editable');return!1}
if(params.recordEditable&&params.recordEditable.id&&!params.hUrl.getId()){params.hUrl.setId(params.recordEditable.id)}
if(!params.url){params.url=params.hUrl.getActionEditableModel()}
if(!params.params){params.params={}}
params.params.isRestFul=!1;if(params.senderButton){if(!params.title){params.title=params.senderButton.text;if(params.paramsExtrasWin&&params.paramsExtrasWin.header===!1){params.title=''}}
if(params.title){params.iconCls=params.senderButton.iconCls}}
if(params.isReadOnlyAccess!=undefined&&params.isReadOnlyAccess){params.params.isReadOnlyAccess=(params.isReadOnlyAccess?1:0)}
var resultEditableModel={getWinEditable:function(){return this.winEditable},getEditableModel:function(){return this.editableModel},getEditable:function(){return this.editable},isNew:function(){if(this.winEditable){return this.winEditable.isNewData()}
return!0}};Exj.submit({url:params.url,params:Exj.normalizeParams(params.params),idMask:params.idMask,mask:'Cargando '+params.title,fnSuccess:function(response){var editableModel=response.data;var editable=new Exj.ui.Editable(editableModel);resultEditableModel.editableModel=editableModel;resultEditableModel.editable=editable;if(response.data.data){if(!params.recordEditable){params.recordEditable=response.data.data}
if(!params.idValue){params.idValue=params.recordEditable.id}
if(!params.idValue){var fk=response.data.fieldKey;if(fk){params.idValue=params.recordEditable[fk]}}}
function _renderListModel(refEditableModel,refScope){if(refEditableModel.listChildModels&&Ext.isObject(refEditableModel.listChildModels)){for(var nameListModel in refEditableModel.listChildModels){var itemListModel=refEditableModel.listChildModels[nameListModel];Exj.evalRendererListModel(itemListModel,refScope);_renderListModel(itemListModel,refScope)}}
if(refEditableModel.listParentModels&&Ext.isObject(refEditableModel.listParentModels)){for(var nameListModel in refEditableModel.listParentModels){var itemListModel=refEditableModel.listParentModels[nameListModel];Exj.evalRendererListModel(itemListModel,refScope);_renderListModel(itemListModel,refScope)}}};if(editableModel.listChildModels||editableModel.listParentModels){_renderListModel(editableModel,scope)}
if(editableModel.editableChildModels){function _renderEditableModel(refEditableModel,refEditable){if(!refEditableModel.editableChildModels){return}
if(!Ext.isObject(refEditableModel.editableChildModels)){return}
if(refEditableModel.listChildModels||refEditableModel.listParentModels){_renderListModel(refEditableModel,scope)}
if(!refEditable.editableChildModels){refEditable.editableChildModels={}}
for(var nameEditableModel in refEditableModel.editableChildModels){var itemEditableModel=refEditableModel.editableChildModels[nameEditableModel];refEditable.editableChildModels[nameEditableModel]=new Exj.ui.Editable(itemEditableModel);if(itemEditableModel.listChildModels){refEditable.editableChildModels[nameEditableModel].listChildModels=itemEditableModel.listChildModels}
if(itemEditableModel.listParentModels){refEditable.editableChildModels[nameEditableModel].listParentModels=itemEditableModel.listParentModels}
_renderEditableModel(itemEditableModel,refEditable.editableChildModels[nameEditableModel])}};_renderEditableModel(editableModel,editable)}
if(params.fnSuccess){params.fnSuccess(editable,editableModel)}
_showWinEditable(editable,editableModel,resultEditableModel)}});function _showWinEditable(editable,editableModel,refResEditableModel){if(params.getItemsUI){var itemsUI=params.getItemsUI(editable,editableModel);if(itemsUI){if(Ext.isArray(itemsUI)){for(var i=0,itemUI;i<itemsUI.length;i++){itemUI=itemsUI[i];params.items.push(itemUI)}}else{params.items.push(itemsUI)}}}
var _cfgFormPanel=new Object();_cfgFormPanel.labelWidth=params.labelWidth;if(params.autoMaximizeWin){}
var paramsWinSubmit={title:params.title,isReadOnlyAccess:params.isReadOnlyAccess,recordEditable:params.recordEditable,hUrl:params.hUrl,nameEntity:params.title,iconCls:params.iconCls,width:params.width,autoHeight:params.autoHeight,height:params.height,autoScroll:params.autoScroll,fnIsValid:params.fnIsValid,fnIsValidBefore:params.fnIsValidBefore,fnIsValidAfter:params.fnIsValidAfter,fnSuccess:params.fnSuccessSave,fnGetDataChangesExtras:params.fnGetDataChangesExtras,onlyEnabledDataChange:params.onlyEnabledDataChange,idValue:params.idValue,isSuccessActionReset:params.isSuccessActionReset,fnSuccessActionReset:params.fnSuccessActionReset,fnBeforeCancel:params.fnBeforeCancel,isSuccessActionNone:params.isSuccessActionNone,buttonsExtras:params.buttonsExtras,textOk:params.textOk,reloadStoreAppMainActionAll:params.reloadStoreAppMainActionAll,closable:params.closable,fnBeforeSubmit:params.fnBeforeSubmit};if(params.textCancel){paramsWinSubmit.textCancel=params.textCancel}
if(params.iconClsOk){paramsWinSubmit.iconClsOk=params.iconClsOk}
if(params.iconClsCancel){paramsWinSubmit.iconClsCancel=params.iconClsCancel}
if(params.paramsExtrasWin&&Ext.isObject(params.paramsExtrasWin)){paramsWinSubmit=Ext.apply(paramsWinSubmit,params.paramsExtrasWin)}
if(params.paramsExtrasFormPanel&&Ext.isObject(params.paramsExtrasFormPanel)){_cfgFormPanel=Ext.apply(_cfgFormPanel,params.paramsExtrasFormPanel)}
if(params.onlyModeLocal!==undefined){paramsWinSubmit.onlyModeLocal=params.onlyModeLocal}
if(params.fnClientSuccess!==undefined){paramsWinSubmit.fnClientSuccess=params.fnClientSuccess}
if(params.fnIsValid!==undefined){paramsWinSubmit.fnIsValid=params.fnIsValid}
if(params.renderToContainer&&params.renderToContainer instanceof Ext.Container){paramsWinSubmit.renderTo=params.renderToContainer.getEl()}
var winSubmitEditable=new Exj.WinSubmit(paramsWinSubmit,_cfgFormPanel);refResEditableModel.winEditable=winSubmitEditable;winSubmitEditable.fnGetParamsData=function(senderWin,basicForm){var paramsData=params.params;if(paramsData.isRestFul!==undefined){paramsData.isRestFul=!0}
if(params.fnGetParamsData){var pExtras=params.fnGetParamsData(senderWin,basicForm);paramsData=Ext.apply(pExtras,paramsData)}
return[paramsData]};for(var i=0,item;i<params.items.length;i++){item=params.items[i];winSubmitEditable.addToForm(item)}
if(editableModel.data){winSubmitEditable.bindToContainer(editableModel.data)}else{if(params.recordEditable){winSubmitEditable.bindToContainer(params.recordEditable)}}
if(params.recordEditable||editableModel.data){winSubmitEditable.addListener('show',function(senderWin){var combos=senderWin.findByType(Ext.form.ComboBox);if(!combos){return}
for(var i=0,combox;i<combos.length;i++){combox=combos[i];if(!combox.lazyValue||combox.getValue()==combox.lazyValue){continue}
Exj.loadValueCombo({combo:combox,elMask:senderWin.getEl(),isValueOriginal:!0})}})}
if(params.autoMaximizeWin){winSubmitEditable.addListener('show',function(senderWin){senderWin.maximize()})}
if(params.fnAfterShowWin){winSubmitEditable.addListener('show',function(senderComponent){params.fnAfterShowWin(senderComponent,senderComponent.isNew)})}
if(params.fnBeforeShowWin){params.fnBeforeShowWin(winSubmitEditable)}
if(params.renderToContainer&&params.renderToContainer instanceof Ext.Container){}
winSubmitEditable.show();return winSubmitEditable};return resultEditableModel};Exj.showDeleteImport=function(params,scope){params=Ext.apply({hUrl:null,title:'',autoAddList:!1,params:{},senderButton:null,width:Exj.calcWidth(60),items:new Array(),recordEditable:null,labelWidth:45},params);if(!params.hUrl){Exj.moe('No se indicó hUrl');return!1}
params.url=params.hUrl.getActionDeleteImportGetItems();params.params.isRestFul=!1;if(params.senderButton){if(!params.title){params.title=params.senderButton.text}
params.iconCls=params.senderButton.iconCls}
Exj.submit({url:params.url,params:params.params,idMask:params.idMask,mask:'Cargando '+params.title,fnSuccess:function(response){var cfgCombo=response.data;if(!cfgCombo){return}
var cmbFiles=Exj.newComboBox(cfgCombo);if(params.fnSuccess){params.fnSuccess(cmbFiles)}
_showWinDelImport(cmbFiles)}});function _showWinDelImport(cmbDelImport){var winSubmitDel;var txfSize=new Ext.form.TextField({fieldLabel:'Tamaño',anchor:'90%',readOnly:!0});var txfType=new Ext.form.TextField({fieldLabel:'Tipo',anchor:'96%',readOnly:!0});var txfNumDel=new Ext.form.TextField({fieldLabel:'Registros',anchor:'96%',readOnly:!0});cmbDelImport.addListener('select',function(senderCombo,r,index){winSubmitDel.idValue=r.data.value;txfSize.setValue(r.data.size_file);txfType.setValue(r.data.name_type_file);txfNumDel.setValue(r.data.nro_del)});params.items.push(cmbDelImport);var pnlInfoFile=Exj.newPanelCols({defaults:{border:!1,xtype:'fieldset',labelWidth:54},items:[{columnWidth:0.55,labelWidth:63,items:[txfType,txfNumDel]},{columnWidth:0.39,items:[txfSize]}]});params.items.push(pnlInfoFile);if(params.getItemsUI){var itemsUI=params.getItemsUI(editable,editableModel);if(itemsUI){if(Ext.isArray(itemsUI)){for(var i=0,itemUI;i<itemsUI.length;i++){itemUI=itemsUI[i];params.items.push(itemUI)}}else{params.items.push(itemsUI)}}}
winSubmitDel=Exj.newWinSubmitDel({title:params.title,recordEditable:params.recordEditable,idValue:0,urlSubmit:params.hUrl.getActionDeleteImportConfirmed(),nameEntity:'Imported data',iconCls:params.iconCls,width:params.width,fnIsValid:params.fnIsValid,fnSuccess:params.fnSuccessDel},{labelWidth:params.labelWidth});winSubmitDel.fnGetParamsData=function(senderWin,basicForm){var paramsData=params.params;if(params.fnGetParamsData){var pExtras=params.fnGetParamsData(senderWin,basicForm);paramsData=Ext.apply(pExtras,paramsData)}
return[paramsData]};for(var i=0,item;i<params.items.length;i++){item=params.items[i];winSubmitDel.addToForm(item)}
winSubmitDel.show();return winSubmitDel};return!0};Exj.showWinReportHTML=function(params,scope){params=Ext.apply({hUrl:null,title:'',url:'',params:{},senderButton:null,width:Exj.calcWidth(90),componentTop:null,componentBottom:null},params);params.report=params.report||{};if(scope){if(scope.hUrl){if(!params.hUrl){params.hUrl=scope.hUrl}
if(!params.report.nameCmp){params.report.nameCmp=scope.hUrl.getOption()}}}
if(!params.hUrl){Exj.moe('No se indicó hUrl','ERROR Exj.showWinReportHTML');return!1}
if(!params.url){params.url=params.hUrl.getActionReportHTML()}
params.params.isRestFul=!1;if(params.senderButton){if(!params.title){params.title=params.senderButton.text}
params.iconCls=params.senderButton.iconCls}
if(!params.title){params.title='Reporte'}
Exj.submit({url:params.url,params:params.params,idMask:params.idMask,mask:'Cargando '+params.title,fnSuccess:function(response){var criteriaModel=response.data.criteriaModel;if(!criteriaModel){Exj.moe('No se obtubo modelo de criteria');return}
if(params.fnSuccess){params.fnSuccess(criteriaModel,response)}
_showWinRepHTML(criteriaModel)}});function _showWinRepHTML(criteriaModel){var itemsWin=new Array();var editableCriteria=new Exj.ui.Editable(criteriaModel);if(!criteriaModel.cfgFormPanel.items){criteriaModel.cfgFormPanel.items=new Array()}
if(params.getContentCriteria){var itemsCriteria=params.getContentCriteria(editableCriteria,criteriaModel);if(itemsCriteria){criteriaModel.cfgFormPanel.items.push(itemsCriteria)}}
var formPanelCriteria=new Ext.form.FormPanel(criteriaModel.cfgFormPanel);itemsWin.push({xtype:'panel',border:!1,bodyCfg:{align:'center'},defaults:{border:!1,layout:'form'},items:formPanelCriteria});function _applyEventsToWin(senderWin,refFormPanelCriteria){if(senderWin.isApplyEvtsToWin){return}
var buttonsCriteria=refFormPanelCriteria.getBottomToolbar();var btnReset=buttonsCriteria.find('exjAction','reset');if(btnReset.length>0){btnReset=btnReset[0];btnReset.addListener('click',function(senderButton,e){refFormPanelCriteria.getForm().reset();senderWin.clearIframeToScreen();if(params.onAfterReset){params.onAfterReset(senderButton,refFormPanelCriteria)}})}
var btnSearch=buttonsCriteria.find('exjAction','search');if(btnSearch.length>0){btnSearch=btnSearch[0];senderWin.callSearch=function(){btnSearch.fireEvent('click',btnSearch,{})};btnSearch.addListener('click',function(senderButton,e){senderWin.loadReportIframeToScreen(!0)});Exj.applyActionPressEnter(formPanelCriteria,senderWin.callSearch)}
senderWin.isApplyEvtsToWin=!0};params.report=Ext.apply({nameTmpl:'nodefinido',printIframeShow:!0},params.report);params.report.pIframeShow=Ext.apply({id:'reporteShow',nameTmpl:params.report.nameTmpl,height:'351px',allowChangeNumSecFac:!1},params.report.pIframeShow);params.report.pIframePrinter=Ext.apply({id:'reportePrinter',nameTmpl:params.report.nameTmpl},params.report.pIframePrinter);itemsWin.push(Exj.newObjPrintShowIframe(params.report.pIframeShow));if(!params.report.printIframeShow){itemsWin.push(Exj.newObjPrintHiddenIframe(params.report.pIframePrinter))}
var win=Exj.newWindow({title:params.title,iconCls:params.iconCls,maximizable:!1,autoHeight:!0,width:params.width,autoWidth:params.autoWidth,items:itemsWin,buttons:[{text:Exj.Idioma('Imprimir'),iconCls:'app-btn-printer',tooltip:'Permite imprimir '+(params.title?params.title:'el reporte'),handler:function(){if(params.report.printIframeShow){var dataRpt=win.readParamsCriteria(!0);if(dataRpt===!1){return}
Exj.printReportIframeShow({scope:win,idIframe:params.report.pIframeShow.id,paramsPrint:params.report.paramsPrint})}else{win.loadIframeToPrinter(!0)}}}]});win.getParamsCriteria=function(){var fb=formPanelCriteria.getForm();if(!fb.isValid()){return!1}
return fb.getFieldValues()};win.loadIframeToPrinter=function(showMsgInfo){var dataRpt=this.readParamsCriteria(showMsgInfo);if(dataRpt===!1){return!1}
if(params.report.printIframeShow){Exj.moe('No se puede cargar iframe printer, porque se indicó que se imprima desde la presentación del mismo.');return!1}
return Exj.loadReportIframeToPrinter({id:params.report.pIframePrinter.id,urlReport:{nameCmp:params.report.nameCmp,dataRpt:dataRpt}})};win.readParamsCriteria=function(showMsgInfo){var paramsCriteria=this.getParamsCriteria()
if(paramsCriteria===!1){if(showMsgInfo){var cmpInvalidCriteria=formPanelCriteria.getForm().items.find(function(cmpCriteria){if(!cmpCriteria.isValid()){return!0}
return!1});Exj.moi('Se requieren valores para el reporte.',function(){if(cmpInvalidCriteria){cmpInvalidCriteria.focus()}})}}
return paramsCriteria};win.loadReportIframeToScreen=function(showMsgInfo){var dataRpt=this.readParamsCriteria(showMsgInfo);if(dataRpt===!1){return}
Exj.loadReportIframeToScreen({id:params.report.pIframeShow.id,urlReport:{nameCmp:params.report.nameCmp,dataRpt:dataRpt}})};win.clearIframeToScreen=function(){Exj.loadReportIframeToScreen({id:params.report.pIframeShow.id,urlReport:{clearURL:!0}})};win.addListener('show',function(senderWinRepHTML){_applyEventsToWin(senderWinRepHTML,formPanelCriteria);senderWinRepHTML.loadReportIframeToScreen()});win.show();return win}};Exj.showListModel=function(params,scope){params=Ext.apply({hUrl:null,title:'Lista',url:'',params:{},senderButton:null,width:Exj.calcWidth(60),componentTop:null,componentBottom:null,showInWindow:!0},params);if(!params.hUrl){Exj.moe('No se indicó hUrl');return!1}
if(!params.url){params.url=params.hUrl.getActionListModel()}
if(!params.params){params.params={}}
params.params.isRestFul=!1;if(params.senderButton){if(params.senderButton.text){params.title=params.senderButton.text}
params.iconCls=params.senderButton.iconCls}
var responseListModel=new Object();var _gridListModel;responseListModel.getGridListModel=function(){return _gridListModel};Exj.submit({url:params.url,params:params.params,idMask:params.idMask,mask:'Cargando '+params.title,fnSuccess:function(response){var listModel=response.data;Exj.evalRendererListModel(listModel,scope);_gridListModel=Exj.newGridPanelFromListModel(listModel,params.hUrl.getActionView());if(params.fnSuccess){params.fnSuccess(_gridListModel,listModel,response)}
if(params.showInWindow){_gridListModel._winGrid=_showWinGrid();_gridListModel.closeWinGrid=function(){this._winGrid.close()}}}});function _showWinGrid(){var itemsWin=new Array();if(params.componentTop){itemsWin.push(params.componentTop)}
itemsWin.push(_gridListModel);if(params.componentBottom){itemsWin.push(params.componentBottom)}
var opcsPrint={hUrl:params.hUrl,_getParamsCriteria:scope._getParamsCriteria,getParamsExtrasReport:function(){return params.params}};Exj.action.grid.onPrints(opcsPrint,_gridListModel);if(params.onActionNew){Exj.action.grid.onNew(_gridListModel,params.onActionNew)}
if(params.onActionEdit){Exj.action.grid.onEdit(_gridListModel,params.onActionEdit)}
if(!params.onActionDel&&params.hUrl){params.onActionDel=function(senderButton,e,r,nameFieldId){if(params.onBeforeDel){if(params.onBeforeDel(senderButton,e,r)===!1){return}}
Exj.executeDelete(_gridListModel,params.hUrl,r,nameFieldId)}}
Exj.action.grid.onDel(_gridListModel,params.onActionDel);Exj.action.grid.onHelp({titleModule:params.title,hUrl:params.hUrl},_gridListModel);var win=Exj.newWindow({title:params.title,iconCls:params.iconCls,maximizable:!1,autoHeight:!0,width:params.width,autoWidth:params.autoWidth,items:itemsWin});win.show();return win};return responseListModel};Exj.showViewModel=function(params,scope){params=Ext.apply({hUrl:null,title:'Ver',url:'',params:{},senderButton:null,width:Exj.calcWidth(60),componentTop:null,componentBottom:null},params);if(!params.hUrl){Exj.moe('No se indicó hUrl');return!1}
if(!params.url){params.url=params.hUrl.getActionViewModel()}
if(!params.params){params.params={}}
params.params.isRestFul=!1;if(params.senderButton){if(params.title=='Ver'){params.title=params.senderButton.text}
params.iconCls=params.senderButton.iconCls}
var _contentView=null;Exj.submit({url:params.url,params:params.params,idMask:params.idMask,mask:'Cargando '+params.title,fnSuccess:function(response){var viewModel=response.data;var uiViewModel=new Exj.ui.Editable(viewModel);if(params.fnSuccess){params.fnSuccess(uiViewModel,viewModel,response)}
if(params.getContentView){_contentView=params.getContentView(uiViewModel,viewModel)}
_showWinView()}});function _showWinView(){var itemsWin=new Array();if(params.componentTop){itemsWin.push(params.componentTop)}
if(_contentView){itemsWin.push(_contentView)}
if(params.componentBottom){itemsWin.push(params.componentBottom)}
var win=Exj.newWindow({title:params.title,iconCls:params.iconCls,maximizable:!1,autoHeight:!0,width:params.width,items:itemsWin});win.show();return win}};Exj.downloadReportModel=function(params){params=Ext.apply({hUrl:null,title:'Reporte',url:'',params:{},senderButton:null,width:Exj.calcWidth(60),isDownloadFile:!0},params);if(!params.hUrl){Exj.moe('No se indicó hUrl');return!1}
if(!params.url){params.url=params.hUrl.getActionReportModel()}
if(!params.params){params.params={}}
params.params.isRestFul=!1;if(params.senderButton){params.title=params.senderButton.text;params.iconCls=params.senderButton.iconCls;if(!params.params.format){params.params.format=params.senderButton.format}}
if(!params.params.format){Exj.moe('No se indicó el formato del reporte!');return!1}
if(params.returnURL){return Exj.addParamsHref(params.url,params.params)}
Exj.submit({url:params.url,params:params.params,idMask:params.idMask,mask:'Generando '+params.title,timeout:(Exj.Global.timeoutRep>1000?Exj.Global.timeoutRep:30000),fnSuccess:function(response){var dataDownload=response.data;if(!dataDownload){return}
if(!dataDownload.idFile){Exj.moe('Falló la generación del reporte: '+params.title);return}
if(params.fnSuccess){params.fnSuccess(dataDownload.idFile,dataDownload,response)}
if(params.isDownloadFile){Exj.downLoadFile(dataDownload)}else if(dataDownload.canViewFile){Exj.downLoadFile(dataDownload)}}});return!0};Exj.showHTML=function(p){var win=Exj.newWindow(p);if(p.idMask){win.show(p.idMask)}else{win.show()}
return win};Exj.showMsg=function(Msg){if(!Msg){Exj.moe('Error desconocido, contáctese con el Admnitrador.');return!0}
if(Msg.type==Exj.Const._EXJ_MSG_TIPO_NINGUNO){return!1}
if(!Msg.text){Msg.text='Error. No se ha enviado el error o motivo.'}
if(!Msg.title){Msg.title=Exj.TITLE}
if(Msg.type==Exj.Const._EXJ_MSG_TIPO_NOTIFY){Exj.mou(Msg.text,Msg.title);return!1}
if(Msg.type==Exj.Const._EXJ_MSG_TIPO_HTML){var _width=Exj.calcWidth(66);var _height=Exj.calcHeight(66);if(Msg.text.length<=333){_width=Exj.calcWidth(42);_height=Exj.calcHeight(33)}
Exj.showHTML({title:Msg.title,html:Msg.text,width:_width,height:_height});return!1}
var icon=Ext.MessageBox.INFO;if(Msg.type==Exj.Const._EXJ_MSG_TIPO_INFO){icon=Ext.MessageBox.INFO}else if(Msg.type==Exj.Const._EXJ_MSG_TIPO_ERROR){icon=Ext.MessageBox.ERROR}else if(Msg.type==Exj.Const._EXJ_MSG_TIPO_WARNING){icon=Ext.MessageBox.WARNING}
var msgShow=Msg.text;if(Msg.title!='ERROR EN CONSULTA SQL'&&(Msg.title!='Existen errores SQL')){msgShow=Msg.text}
Exj.showMessageBox({msg:msgShow,icon:icon,title:Msg.title});return(icon==Ext.MessageBox.ERROR)};Exj.splitTextHTMLPoint=function(text){var i=-1;var html='<b>';var openBold=!0;var nChars=text.length;var c;var txtExcep='ERROR: BASE DE DATOS';if(nChars>=txtExcep.length){var subText=text.substring(0,txtExcep.length);if(subText==txtExcep){return text}}
while(++i<nChars){c=text.substring(i,i+1);if(c==':'){if(openBold){html+='</b>';openBold=!1}}
html+=c;if(i+6<nChars){if(c=='.'){html+='<br>';if(!openBold){openBold=!0;html+='<b>'}}}}
if(openBold){html+='</b>'}
return html};Exj._onFailure=function(responseText,errorMsg,fnCallBackFailure){var failureFromServer=!0;if(!responseText){failureFromServer=!1;Exj.moe(errorMsg)}else{if(errorMsg&&responseText){failureFromServer=!1;Exj.showHTML({title:'Error en el servidor. Referencia:',html:'Error en UI: '+errorMsg+'<br/>'+responseText})}else{Exj.showHTML({title:'Error en el servidor. Referencia:',html:responseText})}}
if(fnCallBackFailure&&Ext.isFunction(fnCallBackFailure)){fnCallBackFailure(responseText,failureFromServer)}};Exj.submit=function(config){if(config.confirm&&config.confirm.msg){config.confirm.fnYes=function(){config.confirm.msg=null;Exj.submit(config,null)};Exj.msgQuestion(config.confirm);return!0}
var autoMethod=!0;if(config&&config.method){autoMethod=!1}
config=Ext.apply({method:'GET',url:'',hUrl:null,isUrlWithExtras:!1,mask:'Por favor espere...',showResult:!0,withoutMask:!1,params:{},timeout:30000,fnSuccess:'',fnFailure:''},config);var me=this;this.params=config.params;this.idMask=config.idMask;this.mask=config.mask;this.fnSuccessError=config.fnSuccessError;this.withoutMask=config.withoutMask;this.showResult=config.showResult;function _getUrl(){if(config.url){if(config.isUrlWithExtras){return config.url}
return Exj.addParamsURL(config.url)}
if(!config.hUrl){return''}
if(!(config.hUrl instanceof Exj.HUrl)){alert('Se ha enviado como parámetro hUrl, pero no es una instancia de la clase: Exj.HUrl');return''}
return config.hUrl.getUrlFromMethod(config.method)};config.url=_getUrl();if(!config.url){Exj.moe('Error de Programación. No se ha especificado la url al instanciar la clase: Exj.submit()');return}
if(autoMethod&&config.method=='GET'){if(config.url.indexOf('/commitChanges?')>0){config.method='POST'}}
if(!this.idMask){if(!this.withoutMask){this.idMask=Ext.getBody()}else{this.idMask=''}}else if(Ext.isFunction(this.idMask.getEl)){this.idMask=this.idMask.getEl()}
if(!this.mask){if(this.withoutMask){this.mask=''}}
this.mask=Exj.Idioma(this.mask);if(!this.withoutMask){this.idMask.mask(this.mask)}
config.success=function(response,e){if(!me.withoutMask){me.idMask.unmask()}
var rJson=response.responseText;var r=null,errorDecodeResponse='';try{r=Ext.util.JSON.decode(rJson)}catch(e){errorDecodeResponse=e}
if(errorDecodeResponse){Exj._onFailure(rJson,errorDecodeResponse,config.fnFailure);return}
if(!Exj.isSuccessResponse(r,config.fnFailure)){return}
if(config.fnSuccess){config.fnSuccess(r)}};config.failure=function(response,e){if(me.idMask){me.idMask.unmask()}
var r=response.result;if(!r){var resServer=response.responseText;if(resServer){Exj._onFailure(r,resServer,config.fnFailure)}else{if(config.fnFailureShowMsg){config.fnFailureShowMsg(response,e)}else{Exj._onFailure('','Conexión fallida. Intente otra vez...',config.fnFailure)}}
return}
Exj._onFailure(r,'',config.fnFailure)};if(config.disableCaching==undefined){config.disableCaching=!0}
return Ext.Ajax.request(config)};Exj.submitAction=function(cfg){cfg=Ext.apply({params:{},url:'',withParams:!0,forceSaveDataChangedEmpty:!1},cfg);if(!cfg.url){Exj.moe('No indicó la url al llamar a: Exj.submitAction','ERROR DE IMPLEMENTACION');return}
if(cfg.withParams){cfg.params.isRestFul=!1}
if(cfg.forceSaveDataChangedEmpty){if(!cfg.params.dataChanged){cfg.params.dataChanged={forceSaveDataChangedEmpty:!0}}}
if(cfg.params.dataChanged&&Ext.isObject(cfg.params.dataChanged)){cfg.params.dataChanged=Ext.encode(cfg.params.dataChanged)}
if(cfg.senderButton&&cfg.senderButton.text){if(!cfg.mask){cfg.mask=cfg.senderButton.text}}
return Exj.submit(cfg)};Exj.isSuccessResponse=function(objResponse,fnSuccessError){var response=null;if(objResponse instanceof Ext.data.Store){response=objResponse.reader.jsonData;if(!response){if(objResponse.reader.meta.root=='DataTopics.topics'){Exj.moe('Hay problemas en el servidor. Posible Error de sintaxis, revize por favor.<br />Contáctese con el Administrador del Sistema.')}else{Exj.moe('No se han servido datos con la estructura: DataTopics.<br />Contáctese con el Administrador del Sistema.')}
return!1}}else{response=objResponse}
if(!response){Exj.moe('No response el servidor!');return!1}
if(response.dataBuffer===undefined||response.data===undefined){Exj.moe('Se obtuvo respuesta del servidor, pero no es estructura esperada.<br/>Informe de esto al Administrador del Framework de la aplicación.','ERROR');return!1}
if(response.dataBuffer){Exj.showDataBuffer(response.dataBuffer)}
if(Exj.requireLoginUser(response.data)){return!1}
if(Exj.isOffline(response.data)){return!1}
if(response.data&&response.data._reloadApp){if(!response.data._reloadApp.msgToShow){response.data._reloadApp.msgToShow='Han cambiado configuraciones en el sistema. El Sistema se recargará.'}
Exj.moi(response.data._reloadApp.msgToShow,response.data._reloadApp.titleToShow,Exj.reloadDocument);return!1}
if(response.status!=Exj.Const._EXJ_ESTADO_OK){if(response.data&&response.data.forceExit){Exj.forceExitSystem(response.Msg.text)}else{Exj.showMsg(response.Msg)}
if(response.Msg.type==Exj.Const._EXJ_MSG_TIPO_ERROR){if(fnSuccessError){fnSuccessError(response,response.Msg.text)}}
return!1}
if(response.Msg.text){Exj.showMsg(response.Msg);if(response.Msg.type==Exj.Const._EXJ_MSG_TIPO_ERROR){if(fnSuccessError){fnSuccessError(response,response.Msg.text)}
return!1}};return!0};Exj.getValuePorcentual=function(Valor1,Valor2){var PRIVAL;var SEGVAL;var VFINAL;if(Valor1>Valor2){PRIVAL=Valor1;SEGVAL=Valor2}else{PRIVAL=Valor2;SEGVAL=Valor1}
if(SEGVAL==0){return 0}
VFINAL=((PRIVAL*100/SEGVAL)-100);return Exj.round(VFINAL)};Exj.newFieldSet=function(config){config.autoHeight=!0;if(config.hideBorders===undefined){config.hideBorders=!1}
if(config.bodyStyle==undefined){config.bodyStyle=Exj.Panel.bodyStyle}
if(config.style==undefined){config.style=Exj.Panel.style}
if(config.hideBorders){config.bodyStyle='padding:0';config.style='padding:0';config.baseCls='x-panel'}
var _fs=new Ext.form.FieldSet(config);return _fs};Exj.getRecordFromComboSelected=function(combo,isIntValue){var r=null;var idValue=combo.getValue();if(!idValue){return r}
if(isIntValue===undefined){isIntValue=!0}
if(isIntValue){idValue=parseInt(idValue)}
combo.getStore().each(function(rCombo){if(rCombo.get('value')==idValue){r=rCombo;return!1}});return r};Exj.PanelCol2FieldSet=function(config){if(!config){Exj.moe('Se debe pasar el config en: Exj.NewPanel2Col');return}
var items1=config.items1;var items2=config.items2;var _title1=config.title1;var _labelWidth1=config.labelWidth1;var _columnWidth1=config.columnWidth1;var _columnWidth2=config.columnWidth2;var _styleCols=config.styleCols;if(_styleCols==undefined){_styleCols=Exj.Panel.style}
var _pMain=new Ext.Panel({bodyStyle:Exj.Panel.bodyStyle,autoHeight:!0,layout:'column',items:[{columnWidth:_columnWidth1,autoHeight:!0,defaultType:'textfield',layout:'fit',style:_styleCols,items:[{xtype:'fieldset',labelWidth:_labelWidth1,autoWidth:!0,autoHeight:!0,title:_title1,items:[items1]}]},{columnWidth:_columnWidth2,autoHeight:!0,defaultType:'textfield',style:_styleCols,tbar:[items2]}]});return{getPanel:function(){return _pMain}}};Exj.newPanel=function(config){config=config||{};var _panel;if(config.colsStylePanel===undefined){config.colsStylePanel=!1}
if(config.layout!='column'){config.colsStylePanel=!1}
if(config.colsStylePanel){config.bodyStyle='padding: 0px 0px 0px 0px;'}
config.defaultType=config.defaultType||'textfield';if(config.bodyStyle==undefined){config.bodyStyle=Exj.Panel.bodyStyle}
if(config.border===undefined){config.border=(config.title?!0:!1)}
if(config.autoHeight==undefined){config.autoHeight=!0}
if(config.items){var i=-1;while(++i<config.items.length){_item=config.items[i];_item.autoHeight=!0;if(_item.border===undefined){_item.border=!1}
if(config.colsStylePanel){_item.baseCls='x-panel';_item.bodyStyle='padding: 3px 3px 3px 3px;';_item.style='padding: 1px 1px 3px 1px;';if(_item.title){_item.border=!0}}}}
_panel=new Ext.Panel(config);return _panel};Exj.newPanelCols=function(config){config=config||{};config.defaults=Ext.apply({border:!1,xtype:'fieldset',labelWidth:39},config.defaults);if(config.defaults.labelAlign=='top'){if(!config.defaults.style){config.defaults.style=''}
config.defaults.style='text-align: left;'+config.defaults.style}
var _pCols;config.layout='column';_pCols=Exj.newPanel(config);return _pCols};Exj.PanelCol2=function(config){if(!config){Exj.moe('Se debe pasar el config en: Exj.PanelCol2');return}
var items1=config.items1;var items2=config.items2;var _columnWidth1=config.columnWidth1;var _columnWidth2=config.columnWidth2;var _styleCols=config.styleCols;if(_styleCols==undefined){_styleCols=Exj.Panel.style}
var _pMain=new Ext.Panel({bodyStyle:Exj.Panel.bodyStyle,autoHeight:!0,layout:'column',items:[{columnWidth:_columnWidth1,autoHeight:!0,defaultType:'textfield',layout:'fit',style:_styleCols,items:[items1]},{columnWidth:_columnWidth2,autoHeight:!0,defaultType:'textfield',style:_styleCols,tbar:[items2]}]});return{getPanel:function(){return _pMain}}};Exj.syncCombos=function(params){if(!params||!params.combo1||!params.combo2){return!1}
var fKey=params.syncField;if(!fKey){fKey=params.combo1.name}
if(!fKey){return!1}
var cmb1=params.combo1,cmb2=params.combo2;_fnFilter();function _fnFilter(value1,valueText){if(value1===undefined){value1=cmb1.getValue();if(value1){value1=parseInt(value1)}}
if(!value1){cmb2.clearValue();cmb2.setDisabled(!0);return}else{cmb2.setDisabled(!1)}
cmb2.store.filterBy(function(rFilter,id){if(!valueText){return(value1==rFilter.get(fKey))}
if(value1!=rFilter.get(fKey)){return!1}
var valueExp=this.store.data.createValueMatcher(valueText);return valueExp.test(rFilter.data.text)},cmb2);if(valueText){return}
if(cmb2.store.getCount()==0){cmb2.clearValue();cmb2.setDisabled(!0);return}
if(cmb2.store.getCount()==1){cmb2.setValue(cmb2.store.getAt(0).data.value);return}
var v2=cmb2.getValue();if(v2){var r2=null;cmb2.store.each(function(recordEach){if(v2==recordEach.data.value){r2=recordEach;return!1}});if(r2){if(value1==r2.get(fKey)){return}}}
if(!valueText){cmb2.clearValue()}};if(cmb1.isSyncAddEv){return}
cmb1.addListener('select',function(senderCombo,r,index){_fnFilter(r.data.value);cmb2.focus(!0,66)});cmb2.addListener('expand',function(senderCombo){_fnFilter(cmb1.getValue())});cmb1.isSyncAddEv=!0};Exj.getPropertiesObject=function(params){params=Ext.apply({obj:null,exceptProps:null,useTrim:!1,exceptPrefixProp:''},params);var responsePropsObj={total:0,empties:0,nulls:0,propsEmpties:[],propsNoEmpties:[],isAllEmpties:!0};if(!Ext.isObject(params.obj)){return responsePropsObj}
if(params.exceptProps&&!Ext.isArray(params.exceptProps)){params.exceptProps=params.exceptProps.split(',')}
var valueProp;for(nameProp in params.obj){if(params.exceptProps&&params.exceptProps.length>0){if(Exj.inArray(nameProp,params.exceptProps)){continue}}
if(params.exceptPrefixProp){if(nameProp.indexOf(params.exceptPrefixProp)==0){continue}}
responsePropsObj.total+=1;valueProp=params.obj[nameProp];if(valueProp===undefined){responsePropsObj.propsEmpties.push(nameProp);responsePropsObj.empties+=1;continue}
if(valueProp===null){responsePropsObj.nulls+=1;responsePropsObj.empties+=1;responsePropsObj.propsEmpties.push(nameProp);continue}
if(valueProp===!0||valueProp===!1){responsePropsObj.propsNoEmpties.push(nameProp);continue}
if(valueProp&&(Ext.isObject(valueProp)||Ext.isArray(valueProp))){responsePropsObj.propsNoEmpties.push(nameProp);continue}
valueProp+='';if(params.useTrim){valueProp=valueProp.trim()}
if(valueProp.length==0){responsePropsObj.empties+=1;responsePropsObj.propsEmpties.push(nameProp)}else{responsePropsObj.propsNoEmpties.push(nameProp)}}
responsePropsObj.isAllEmpties=(responsePropsObj.total==responsePropsObj.empties);return responsePropsObj};Exj.newJsonStore=function(config,urlProxy,baseParamsExtras,onlyModeLocal){config=config||{};if(onlyModeLocal===undefined){onlyModeLocal=!1}
var cfgJsonStore=Exj.cloneSmart(config);if(!cfgJsonStore.proxy){if(!urlProxy&&cfgJsonStore.urlProxy){urlProxy=cfgJsonStore.urlProxy}
if(urlProxy){cfgJsonStore.proxy=new Ext.data.ScriptTagProxy({url:urlProxy})}}
if(baseParamsExtras&&Ext.isObject(baseParamsExtras)){cfgJsonStore.baseParams=Ext.applyIf(baseParamsExtras,cfgJsonStore.baseParams)}
if((!cfgJsonStore.proxy&&cfgJsonStore.remoteSort)||onlyModeLocal){cfgJsonStore.remoteSort=!1}
var sto=new Ext.data.JsonStore(cfgJsonStore);Exj.loadException(sto);if(onlyModeLocal||!cfgJsonStore.proxy){sto.idsDeleted=[];sto.addListener('remove',function(senderStore,record,index){var id=Ext.num(record.id,-1);if(id<0){if(record.phantom){return}
id=record.id}
if(Exj.inArray(id,sto.idsDeleted)){return}
sto.idsDeleted.push(id)})}
return sto};Exj.newStoreList=function(config){var _url=config.url;if(!_url){Exj.moe('Se debe pasar la url a la clase: Exj.newStoreList');return''}
if(!config.valueField){config.valueField='value'}
if(!config.displayField){config.displayField='text'}
if(!config.fields){config.fields=[{name:config.displayField,mapping:config.displayField},{name:config.valueField,mapping:config.valueField}]}
if(!config.sortField){config.sortField=config.displayField}
if(!config.sortDir){config.sortDir='asc'}
var proxyStore=new Ext.data.ScriptTagProxy({url:Exj.addParamsURL(_url)});var store=new Ext.data.JsonStore({root:'DataTopics.topics',totalProperty:'DataTopics.total',idProperty:config.valueField,remoteSort:!1,fields:config.fields,proxy:proxyStore});if(config.sortField){store.setDefaultSort(config.sortField,config.sortDir)}
Exj.loadException(store,config.fnLoadData);Exj.loadExceptionProxy(proxyStore);store._fieldsCustom=config.fields;return store};Exj.newComboURL=function(config){if(!config){Exj.moe('Se deben pasar los parametros a la clase: Exj.newComboURL()');return null}
if(!config.url){Exj.moe('Se deben pasar el parámetro: url a la clase: Exj.newComboURL()');return null}
if(config.clearValueNoFound===undefined){config.clearValueNoFound=!0}
if(config.autoSelectFirst===undefined){config.autoSelectFirst=!0}
if(config.emptyText===undefined){config.emptyText='Select...'}
config.emptyText=Exj.Idioma(config.emptyText);if(config.clearable===undefined){config.clearable=!1}
if(config.listClass==undefined){config.listClass='item-combo-span'}
config.fieldLabel=Exj.Idioma(config.fieldLabel);if(config.labelSeparator===undefined){config.labelSeparator=':'}
if(!config.pageSize){config.pageSize=Exj.LIMIT}
if(!config.listWidth){config.listWidth=Exj.listWidth}
if(!config.displayField){config.displayField='text'}
if(!config.valueField){config.valueField='value'}
if(config.fields==undefined){config.fields=''}
if(config.typeAhead===undefined){config.typeAhead=!0}
if(config.selectOnFocus===undefined){config.selectOnFocus=!0}
if(config.hideTrigger===undefined){config.hideTrigger=!1}
if(config.loadingText===undefined){config.loadingText='Looking...'}
config.loadingText=Exj.Idioma(config.loadingText);if(config.triggerAction===undefined){config.triggerAction='all'}
if(!config.tplContent){config.tplContent='{'+config.displayField+'}'}
if(config.tpl==undefined){config.tpl=new Ext.XTemplate('<tpl for="."><div class="search-item" >',config.tplContent,'</div></tpl>')}
config.itemSelector='';if(config.tpl){config.itemSelector='div.search-item'}
var _RecordCombo='';if(config.store===undefined){config.store=Exj.newStoreList({url:config.url,fnLoadData:config.fnLoadData,valueField:config.valueField,displayField:config.displayField,fields:config.fields,sortField:config.sortField,sortDir:config.sortDir});config.fields=config.store._fieldsCustom}
if(config.fields){_RecordCombo=Ext.data.Record.create(config.fields)}else{alert('ERROR DE PROGRAMACION. No se han especificado fields para el combo: '+config.valueField)}
config.mode='remote';var combo;if(config.clearable){combo=new Ext.form.ClearableComboBox(config);if(config.onClearValueOnce){combo.setOnClearValue(function(){if(combo._recordSelected){config.onClearValueOnce(combo._recordSelected);combo._recordSelected=null}})}}else{combo=new Ext.form.ComboBox(config)}
combo._recordSelected=null;function _isSelected(record){var _selected=!1;if(combo._recordSelected==null){return _selected}
if(!combo._recordSelected.data){combo._recordSelected=null;return _selected}
if(combo._recordSelected.data.value==record.data.value){if(combo.getValue()==record.data.value){_selected=!0}else{combo._recordSelected=Exj.getRecordSelectedFromCombo(combo,!1)}}
return _selected};combo.store.addListener('load',function(sender,records,options){var _query='';if(combo.store&&combo.store.baseParams){_query=combo.store.baseParams.query}
if(records.length==0){combo._recordSelected=null;if(combo.store&&combo.store.baseParams){if(config.clearValueNoFound){if(_query){combo.clearValue();Exj.mou('Not found: '+'<span style="color:blue;">'+_query+'</span>',combo.fieldLabel);combo.focus(!0,21)}}}
if(config.fnOnLoadNoSelect){config.fnOnLoadNoSelect(records,records.length)}
return}
if(records.length>1){combo._recordSelected=null;if(config.fnOnLoadNoSelect){config.fnOnLoadNoSelect(records,records.length)}
return};if(config.autoSelectFirst){if(_query){return}
var r=records[0];combo.fireEvent('select',combo,r,0);combo.collapse()}});function _setRecordSelectedCombo(r,index){if(_isSelected(r)){return}
if(combo.getValue()!=r.data.value){combo.setValue(r.data.value)}
combo._recordSelected=r;if(config.fnOnSelect){config.fnOnSelect(r,index)}};combo.addListener('blur',function(senderCombo){var r=Exj.getRecordSelectedFromCombo(combo,!1);if(!combo.getRawValue()&&r){combo.setRawValue(r.data.text)}});combo.addListener('beforeselect',function(senderCombo,r,index){if(combo._recordSelected){if(combo._recordSelected.data){if(combo._recordSelected.data.value!=combo.getValue()){combo._recordSelected=null}}else{combo._recordSelected=null}}
if(!combo._recordSelected){combo._recordSelected=Exj.getRecordSelectedFromCombo(combo,!1)}
if(r.data.value==combo.getValue()){if(combo.fieldLabel){Exj.mou(Exj.Idioma('It is selected')+':<br /><b>'+r.data.text+'</b>',combo.fieldLabel)}else{Exj.mou('It is selected: '+r.data.text)}
if(!combo.getRawValue()){combo.setRawValue(r.data.text)}
combo.collapse();return!1}
return!0});combo.addListener('select',function(sender,r,index){_setRecordSelectedCombo(r,index)});combo.getRecordSelected=function(){if(combo._recordSelected==null){combo._recordSelected=Exj.getRecordSelectedFromCombo(combo)}
return combo._recordSelected};combo.clearBaseParams=function(){combo.store.baseParams={};combo.clearValue()};combo.setBaseParams=function(params){combo.store.baseParams=params;if(combo.getValue()){combo.clearValue()}};combo.setValueData=function(params){if(!params.data){Exj.moe('ERROR DE PROGRAMACION.<br />No se ha especificado el parametro: params.data<br />En la funcion: combo.setValueData del Componente base.<br />Ref: '+config.fieldLabel);return}
var data=params.data;var baseParams=params.baseParams;var _s=combo.store;_s.clearFilter();var bpEqual=!1;if(_s.baseParams&&baseParams){if(Ext.encode(_s.baseParams)==Ext.encode(baseParams)){bpEqual=!0}}
if(baseParams&&!bpEqual){_s.baseParams=baseParams}
if(!data.value){data.value=0}
data.value=parseInt(data.value);var idCombo=data.value;if(idCombo==0){if(!bpEqual){_s.removeAll()}
combo.clearValue();return}
var recordFound=!1;var indexRecord=-1;_s.each(function(r){++indexRecord;if(r.data.value==idCombo){recordFound=r;return!1}});if(recordFound){combo.fireEvent('select',combo,recordFound,indexRecord);return}
++indexRecord;if(!bpEqual){_s.removeAll();indexRecord=0}
if(!_RecordCombo){Exj.moe('Error. No se ha cargado la clase del registro del combo.<br />No se pudo agregar registro al combo para ID: '+idCombo);return}
var newRecordCombo=new _RecordCombo(data);_s.add(newRecordCombo);_s.commitChanges();combo.fireEvent('select',combo,newRecordCombo,indexRecord)};combo.storeLoad=function(paramsLoad){var _testLoad=!1;if(!paramsLoad){paramsLoad=new Object();paramsLoad.params=new Object();paramsLoad.baseParams=undefined;paramsLoad.clearValue=!0}
if(paramsLoad.clearValue===undefined){paramsLoad.clearValue=!0}
if(paramsLoad.autoDisabled===undefined){paramsLoad.autoDisabled=!1}
var bp=paramsLoad.baseParams;var p=paramsLoad.params;if(combo.store.baseParams){if(combo.store.baseParams.query!=undefined){bp.query='';combo.store.baseParams.query=''}}
if(p==undefined){p=new Object()}
if(p.start==undefined){p.start=0}
if(p.limit==undefined){p.limit=Exj.LIMIT}
var pTest={start:0,limit:Exj.LIMIT};if(Ext.encode(pTest)==Ext.encode(p)){if(_testLoad){alert('TEST storeLoad() COMPROBANDO 1 bp: '+Ext.encode(combo.store.baseParams)+' == '+Ext.encode(bp))}
if(combo.store.baseParams&&bp){if(Ext.encode(combo.store.baseParams)==Ext.encode(bp)){if(_testLoad){Exj.mou('TEST storeLoad() Evitando recarga Por baseParams Iguales',combo.fieldLabel)}
return}}else if(bp==undefined){if(_testLoad){Exj.mou('TEST storeLoad() Evitando recarga Por params Iguales y no definicion de baseParams',combo.fieldLabel)}
return}}
if(paramsLoad.clearValue){if(combo.getValue()){combo.clearValue()}}
if(bp!=undefined){combo.store.baseParams=bp}
if(paramsLoad.autoDisabled){combo.setDisabled(!0)}
combo.store.load({params:p,callback:function(r,options,success){if(paramsLoad.autoDisabled){combo.setDisabled(!1)}
if(!success){return}
if(paramsLoad.callbackSuccess){paramsLoad.callbackSuccess(r,options)}}});if(paramsLoad.successLoad){paramsLoad.successLoad(p)}};combo.setValueExecuteOnSelect=function(newValue){combo.setValue(newValue);if(!combo.getValue()){return!1}
var r=Exj.getRecordSelectedFromCombo(combo,!1);if(!r){combo._recordSelected=null;return!1}
combo._recordSelected=r;if(config.fnOnSelect){config.fnOnSelect(combo._recordSelected,0)}
return combo._recordSelected};combo.addRecord=function(record,selectRec,selectAndExecuteSelect){return Exj.addRecordCombo(combo,record,selectRec,selectAndExecuteSelect)};return combo};Exj.fireEventSelect=function(combo){var idValue=combo.getValue();if(!idValue){return!1}
var sto=combo.getStore();idValue=parseInt(idValue);var indexValue=sto.findExact('value',idValue);if(indexValue<0){Exj.moe('There was no value of ID: '+idValue,'ERROR '+combo.fieldName);return!1}
combo.fireEvent('select',combo,sto.getAt(indexValue),indexValue);return!0};Exj.setValueSmartRecordCombo=function(refCombo,newValue,selectAndExecuteSelect){if(selectAndExecuteSelect===undefined){selectAndExecuteSelect=!1}
if(refCombo.getValue()==newValue){if(refCombo.getRawValue()!=newValue){return!0}}
var foundItem=!1;refCombo.store.each(function(r){if(r.data.value==newValue){foundItem=!0}});if(!foundItem){return!1}
if(selectAndExecuteSelect){refCombo.setValueExecuteOnSelect(newValue)}else{refCombo.setValue(newValue)}
return!0};Exj.addRecordCombo=function(refCombo,record,selectRec,selectAndExecuteSelect){if(!record){return!1}
if(record.data==undefined){return!1}
if(selectRec===undefined){selectRec=!0}
if(selectAndExecuteSelect===undefined){selectAndExecuteSelect=!1}
refCombo.store.add(record);if(selectRec){if(selectAndExecuteSelect){refCombo.setValueExecuteOnSelect(record.data.value)}else{refCombo.setValue(record.data.value)}}
return!0};Exj.newComboArray=function(config){if(config.autoSelectFirst===undefined){config.autoSelectFirst=!0}
if(config.showMsgNoFound===undefined){config.showMsgNoFound=!0}
if(config.clearable==undefined){config.clearable=!1}
if(!config.displayField){config.displayField='text'}
if(!config.valueField){config.valueField='value'}
if(config.fieldLabel==undefined){config.fieldLabel=''}
config.fieldLabel=Exj.Idioma(config.fieldLabel);if(config.listWidth===undefined){config.listWidth=Exj.listWidth}
if(config.listClass==undefined){config.listClass='item-combo-span'}
if(!config.width){config.width=0}
if(config.disabled==undefined){config.disabled=!1}
if(config.tplContent==undefined){config.tplContent=''}
if(config.tpl==undefined){config.tpl='';if(config.tplContent){config.tpl=new Ext.XTemplate('<tpl for="."><div class="search-item" >',config.tplContent,'</div></tpl>')}}
config.itemSelector='';if(config.tpl){config.itemSelector='div.search-item'}
if(config.storeFields==undefined){config.storeFields=[config.valueField,config.displayField]}
if(!config.data){config.data=[[0],[0]]}
if(config.store==undefined){config.store=new Ext.data.SimpleStore({fields:config.storeFields,data:config.data})}else{}
if(config.emptyText==undefined){config.emptyText=Exj.Idioma('Select...')}
if(config.typeAhead===undefined){config.typeAhead=!0}
if(config.triggerAction==undefined){config.triggerAction='all'}
if(config.selectOnFocus==undefined){config.selectOnFocus=!1}
config.mode='local';config.loadingText=Exj.Idioma('Looking...');var _cmbArray;if(config.clearable){_cmbArray=new Ext.form.ClearableComboBox(config);if(config.onClearValueOnce){_cmbArray.setOnClearValue(function(){if(_cmbArray._recordSelected){config.onClearValueOnce(_cmbArray._recordSelected);_cmbArray._recordSelected=null}})}}else{_cmbArray=new Ext.form.ComboBox(config)}
_cmbArray._recordSelected=null;function _isSelected(record){var _selected=!1;if(_cmbArray._recordSelected==null){return _selected}
if(!_cmbArray._recordSelected.data){_cmbArray._recordSelected=null;return _selected}
if(_cmbArray._recordSelected.data.value==record.data.value){if(_cmbArray.getValue()==record.data.value){_selected=!0}else{_cmbArray._recordSelected=Exj.getRecordSelectedFromCombo(_cmbArray,!1)}}
return _selected};_cmbArray.addListener('blur',function(senderCombo){var r=Exj.getRecordSelectedFromCombo(_cmbArray,!1);if(!_cmbArray.getRawValue()&&r){_cmbArray.setRawValue(r.data.text)}});_cmbArray.addListener('beforeselect',function(senderCombo,r,index){if(_cmbArray._recordSelected){if(_cmbArray._recordSelected.data){if(_cmbArray._recordSelected.data.value!=_cmbArray.getValue()){_cmbArray._recordSelected=null}}else{_cmbArray._recordSelected=null}}
if(!_cmbArray._recordSelected){_cmbArray._recordSelected=Exj.getRecordSelectedFromCombo(_cmbArray,!1)}
if(r.data.value==_cmbArray.getValue()){if(_cmbArray.fieldLabel){if(index>0){Exj.mou(Exj.Idioma('It is selected')+':<br /><b>'+r.data.text+'</b>',_cmbArray.fieldLabel)}}else{Exj.mou(Exj.Idioma('It is selected')+': '+r.data.text)}
if(!_cmbArray.getRawValue()){_cmbArray.setRawValue(r.data.text)}
_cmbArray.collapse();return!1}
return!0});_cmbArray.addListener('select',function(sender,r,index){if(_isSelected(r)){return}
_cmbArray._recordSelected=r;if(config.fnOnSelect){config.fnOnSelect(r,index)}});Exj.eventComboClearFilterBlur({objComboBox:_cmbArray,fnOnBlur:config.fnOnBlur});_cmbArray.getRecordSelected=function(){return _cmbArray._recordSelected};_cmbArray.setValueExecuteOnSelect=function(newValue){_cmbArray.setValue(newValue);var r=Exj.getRecordSelectedFromCombo(_cmbArray,!1);if(!r){_cmbArray._recordSelected=null;return!1}
_cmbArray._recordSelected=r;if(config.fnOnSelect){config.fnOnSelect(_cmbArray._recordSelected,0)}
return _cmbArray._recordSelected};function _selectFirstRecord(){if(!_cmbArray.store){_cmbArray._recordSelected=null;return!1}
if(_cmbArray.store.getCount()==0){_cmbArray._recordSelected=null;if(_cmbArray.getValue()&&_cmbArray.view){_cmbArray.clearValue()}
return!1}
if(_cmbArray.store.getCount()>0){_cmbArray._recordSelected=null;return!1}
var r=_cmbArray.store.getAt(0);_cmbArray.setValue(r.data.value);if(_isSelected(r)){return}
_cmbArray._recordSelected=r;if(config.fnOnSelect){config.fnOnSelect(_cmbArray._recordSelected,0)}
return!0};if(config.valueDefault){var rDef=null;var indexDef=-1;_cmbArray.store.each(function(r){++indexDef;if(r.data.value==config.valueDefault){rDef=r;return!1}});if(rDef==null){if(config.showMsgNoFound){alert('ERROR NO SE ENCUENTRA VALOR POR DEFECTO: '+config.valueDefault+' Combo: '+_cmbArray.fieldLabel)}}else{if(!_isSelected(rDef)){_cmbArray.setValue(config.valueDefault);_cmbArray._recordSelected=rDef;if(config.fnOnSelect){config.fnOnSelect(_cmbArray._recordSelected,indexDef)}}}};if(config.autoSelectFirst){_selectFirstRecord()};if(!_cmbArray.getValue()){if(config.fnOnLoadNoSelect){var _records=[];if(_cmbArray.store.getCount()>0){_records=_cmbArray.store.getRange()}
config.fnOnLoadNoSelect(_records,_records.length)}};_cmbArray.clearSelected=function(clearFilterStore){_cmbArray._recordSelected=null;if(clearFilterStore===undefined){clearFilterStore=!0}
if(clearFilterStore){_cmbArray.store.clearFilter(!0)}};_cmbArray.autoLoad=function(p){if(!p.url){Exj.moe('Se debe llamar a la funcion: autoLoad() del combo!!!');return}
if(!p.mask){p.mask='Looking...'}
p.mask=Exj.Idioma(p.mask);Exj.submit({url:p.url,params:p.params,idMask:_cmbArray.getEl(),mask:p.mask,showResult:!1,fnSuccess:function(r){if(!r.success){if(!r.msgError){r.msgError='Error desconocido, originado por el url: '+config.url}
Exj.moe(r.msgError);return!1}
var totalCount=r.totalCount;var t=r.topics;var dataCombo=new Array();var i=-1;while(++i<t.length){var row=t[i];var valValue=0;eval('valValue = row.'+config.valueField);var valText='';eval('valText = row.'+config.displayField);dataCombo[i]=[valValue,valText]}
_cmbArray.store.loadData(dataCombo);if(p.valueDefault&&totalCount>0){_cmbArray.setValue(p.valueDefault)}
return!0}})};_cmbArray.addRecord=function(record,selectRec){return Exj.addRecordCombo(_cmbArray,record,selectRec)};_cmbArray.setValueSmart=function(newValue,selectAndExecuteSelect){return Exj.setValueSmartRecordCombo(_cmbArray,newValue,selectAndExecuteSelect)};return _cmbArray};Exj.comboSelectFirst=function(combo){var st=combo.getStore();if(st.getCount()==0){return!1}
var r=st.getAt(0);if(!r){return!1}
combo.setValue(r.data.value);combo.fireEvent('select',combo,r,0);return r};Exj.newComboNumbers=function(config){if(!config.start){config.start=0}
if(!config.limit){config.limit=config.start}
if(config.emptyText==undefined){config.emptyText=Exj.Idioma('Seleccione')}
if(config.fieldLabel==undefined){config.fieldLabel=''}
if(!config.width){config.width=60}
if(!config.listWidth){config.listWidth=66}
var dataNum=new Array();var i=config.start;var indexData=-1;while(i<=config.limit){var d=new Object();d.value=i;d.text=i;dataNum[++indexData]=d;++i}
var dataRecords=new Object();dataRecords.records=dataNum;var storeNum=new Ext.data.JsonStore({fields:[{name:'value'},{name:'text'}],data:dataRecords,root:'records'});var combo=new Ext.form.ComboBox({store:storeNum,displayField:'text',valueField:'value',typeAhead:!0,mode:'local',triggerAction:'all',emptyText:config.emptyText,selectOnFocus:!1,fieldLabel:config.fieldLabel,width:config.width,forceSelection:!0,listWidth:config.listWidth});return combo};Exj.addEventAutoSelectList=function(params){if(!params){return!1}
if(!params.component){Exj.moe('Debe pasarse el parametro: component en la funcion: Exj.addEventAutoSelectList()');return!1}
var comp=params.component;if(!comp.store){Exj.moe('Función: Exj.addEventAutoSelectList(). El componente pasado, no tiene store.');return!1}
comp.store.addListener('load',function(sender,records,options){var data=Exj.getDatas(comp.store);if(!data){return!1}
if(records.length==0){return!1}
if(params.idList==undefined){comp.setValue(records[0].data.Value)}else{comp.setValue(params.idList)}});return!0};Exj.showLogin=function(config){config=Ext.apply({title:'Login',width:300,isButtonsOkCancel:!0,urlSubmit:'index3.php/globals/loginUser?option=exj_global',waitMsg:'Verificando usuario y contraseña...',fnSuccess:function(form,result,action){var data=result.data;if(data.forceExit){Ext.get('form-login').dom.Submit.click()}}},config);var win=new Exj.WinSubmit(config,{labelWidth:111});var txtNameUsr;var txtPwdUsr;var pLogo;var widthDef='81%';txtNameUsr=Exj.newTextField({fieldLabel:'Cédula/RUC',name:'username',width:widthDef,style:'color: blue;',enableKeyEvents:!0,readOnly:(Exj.Global.LOGIN_USUARIO?!0:!1),allowBlank:!1,blankText:'Se requiere el Nombre de Usuario'});txtPwdUsr=Exj.newTextField({fieldLabel:'Contraseña',name:'passwd',width:widthDef,inputType:'password',enableKeyEvents:!0,allowBlank:!1,blankText:'Se requiere la Contraseña'});var indicacionesHTML='<span class="login-info">';indicacionesHTML+='Se ha terminado el tiempo de sesión.<br />Se requiere su contraseña, para continuar.';indicacionesHTML+='</span>';var logoHTML='<table border="0" >';logoHTML+='<tr>';logoHTML+='<td>'+'<div class="logoSesion" />'+'</td>';logoHTML+='<td>'+indicacionesHTML+'</td>';logoHTML+='</tr>';logoHTML+='</table>';logoHTML+='<br />';pLogo=new Ext.Panel({html:logoHTML,border:!1,autoHeight:!0});win.add(pLogo);win.addToForm(txtNameUsr);win.addToForm(txtPwdUsr);txtNameUsr.setValue(Exj.Global.LOGIN_USUARIO);win.show()};Exj.showDownloadFile=function(config){if(!config.url){Exj.moe('No se ha pasado el parámetro url a la función: Exj.showDownloadFile(...)');return!1}
if(!config.idMask){config.idMask=Ext.getBody()}
if(!config.mask){config.mask='Por favor espere...'}
var s=new Exj.submit({url:config.url,params:config.params,idMask:config.idMask,mask:config.mask,showResult:!1,fnSuccess:function(r){var file=r.data[0];if(file.FullName==undefined){Exj.moe('Error de programación.<br />Debe pasarse datos al cliente con la estructura: Exj.Tables.OpenFile().');return}
if(config.msgSuccess==undefined){config.msgSuccess='Listo para descarga del archivo:'}
if(config.msgSuccess){Exj.mou(config.msgSuccess+' <b>'+file.Name+'</b>')}
var pathFile=file.FullName;var frmOpenFile=new Ext.form.FormPanel({bodyStyle:Exj.bodyStyle,autoHeight:!0,frame:!0});var url='Download.aspx?pathFile='+pathFile;if(!config.titleLink){config.titleLink='Click aquí para descargar el archivo'}
var linkX=new Ext.form.Label({html:'<a  href="'+url+'" target="_blank">'+config.titleLink+'</a>'});var fs=new Ext.form.FieldSet({title:'Información del Archivo',autoHeight:!0,collapsed:!0,collapsible:!0,style:'padding: 3px',labelWidth:111,items:[Exj.newTextField({fieldLabel:'Name',value:file.Name,width:'96%',readOnly:!0}),Exj.newTextField({fieldLabel:'Fecha de creación',value:file.CreationTime,width:'96%',readOnly:!0}),Exj.newTextField({fieldLabel:'Extensión',value:file.Extension,width:'96%',readOnly:!0}),Exj.newTextField({fieldLabel:'Tamaño (bytes)',value:file.Length,width:'96%',readOnly:!0}),new Ext.form.TextArea({fieldLabel:'Descripción',value:file.Descripcion,width:'96%',readOnly:!0})]});frmOpenFile.add(linkX);var win=new Ext.Window({title:config.title,iconCls:'exj-icon-app',layout:'fit',width:333,autoHeight:!0,closeAction:'close',plain:!0,buttons:[{text:'Cerrar',handler:function(){win.destroy()}}]});win.add(frmOpenFile);win.add(fs);win.show(config.idMask)}});return!0};Exj.getRecordSelectedFromCombo=function(combo,valueIsInt){var rSel='';if(valueIsInt===undefined){valueIsInt=!0}
var v=combo.getValue();if(v===''){if(combo.getRawValue()){combo.clearValue()}
return rSel}else{if(valueIsInt){v=parseInt(v)}}
if(combo.store){combo.store.each(function(r){if(r.data.value==v){rSel=r;return!1}})}
return rSel};Exj.delRecordFromGrid=function(params){if(params.title==undefined){params.title=Exj.TITLE}
params.title=Exj.Idioma(params.title);if(params.isGridArray===undefined){params.isGridArray=!1}
if(params.autoRemoveArray===undefined){params.autoRemoveArray=!0}
if(!params.icon){params.icon=Ext.MessageBox.QUESTION}
if(!params.msg){params.msg='Está seguro de remove?'}
params.msg=Exj.Idioma(params.msg);if(!params.animEl){params.animEl=''}
if(!params.mask){params.mask='Removing, please wait...'}
params.mask=Exj.Idioma(params.mask);if(!params.msgSelect){params.msgSelect='Select a record to remove...'}
params.msgSelect=Exj.Idioma(params.msgSelect);if(params.showResult===undefined){params.showResult=!0}
if(!params.dataSelect){var sm=params.grid.getSelectionModel();if(sm.getCount()==0){Exj.mou(params.msgSelect);return!1}
var row=sm.getSelected();params.dataSelect=row.data}
if(!params.fnOk){params.fnOk=function(){return!0}}
Ext.Msg.show({title:params.title,msg:params.msg,buttons:Ext.Msg.YESNO,fn:function(buttonId,text){if(buttonId=='no'){return}
if(params.fnOk){var paramsRef=new Object();paramsRef.itemSel=params.dataSelect;paramsRef.paramsDel=new Object();if(params.fnOk(paramsRef)){if(params.url){Exj.submit({url:params.url,params:paramsRef.paramsDel,idMask:params.grid.getEl(),mask:params.mask,showResult:params.showResult,fnSuccess:function(r){if(params.success){params.success(r,paramsRef.paramsDel)}
if(params.isGridArray){if(params.autoRemoveArray){params.grid.store.remove(params.grid.getSelectionModel().getSelected())}}else{params.grid.store.reload()}}})}else{if(params.isGridArray){if(params.autoRemoveArray){params.grid.store.remove(params.grid.getSelectionModel().getSelected())}}else{params.grid.store.reload()}
if(params.success){params.success(paramsRef.itemSel)}}}}},animEl:params.animEl,icon:params.icon})};Exj.getRecordSelected=function(p){if(!p.grid){Exj.moe('Error no se ha enviado el grid a la funcion: Exj.getRecordSelected()');return!1}
if(p.showMsg===undefined){p.showMsg=!0}
if(p.msgSeleccione==undefined){p.msgSeleccione='Select a row...'}
var sm=p.grid.getSelectionModel();if(sm.getCount()==0){if(p.showMsg){Exj.mou(p.msgSeleccione)}
return!1}
var row=sm.getSelected();return row.data};Exj.Mid=function(text,posIni,nCaracteres){if(!text){return text}
if(nCaracteres===undefined){nCaracteres=text.length}
--posIni;if(posIni<0){posIni=0}
var fin=posIni+nCaracteres;if(fin>text.length){fin=text.length}
return text.substring(posIni,fin)};Exj.getValueLangGlobal=function(keyLang,arrayLang){if(!keyLang){return keyLang}
if(arrayLang==undefined){arrayLang=Exj.Global.dataListLangGlobal}
if(!arrayLang){return keyLang}
if(arrayLang.length==0){return keyLang}
var i=-1;var itemLang;var valueLang='';while(++i<arrayLang.length){itemLang=arrayLang[i];if(!itemLang.value_lang){continue}
if(itemLang.key_lang.trim()==keyLang){valueLang=itemLang.value_lang;break}
if((itemLang.key_lang+'.')==keyLang){valueLang=itemLang.value_lang+'.';break}
if((itemLang.key_lang+'...')==keyLang){valueLang=itemLang.value_lang+'...';break}
if(itemLang.compare_sensitive==0){if(itemLang.key_lang.toUpperCase()==keyLang.toUpperCase()){valueLang=itemLang.value_lang;break}}}
return valueLang};Exj.Idioma=function(textDefault,objIdioma){if(!textDefault){return textDefault}
if((typeof textDefault)=='object'){var txtError='';if(textDefault.message){txtError+=textDefault.message}
if(textDefault.href){txtError+=' href: '+textDefault.href}
if(textDefault.source){txtError+=' source: '+textDefault.source}
if(textDefault.stack){txtError+=' stack: '+textDefault.stack}
return txtError}
if(!textDefault.trim){return''}
if(!textDefault.trim()){return textDefault}
var valueLang=Exj.getValueLangGlobal(textDefault);if(valueLang){return valueLang}
if(!Exj._langCurrent){return textDefault}
valueLang=Exj.getValueLangGlobal(textDefault,Exj._langCurrent);if(valueLang){return valueLang}
return textDefault};Exj.TIENEPERMISO=function(pos,responde){if(responde===undefined){responde=!0}
if(!Exj.Global.PermisoAcceso){Exj.moe('Problemas en: Exj.TIENEPERMISO() no hay info del Map del Usuario.');return!1}
--pos;if(pos<0){pos=0}
if(pos>=Exj.Global.PermisoAcceso.length){Exj.moe('Problemas en: Exj.TIENEPERMISO() la pos es mayor a Map del Usuario: pos: '+pos);return!1}
var permiso=Exj.Global.PermisoAcceso.substring(pos,pos+1);if(permiso=='1'){return!0}else{if(responde){Exj.moi('¡Acceso Denegado!<br />El grupo de: '+Exj.Global.GRUPUSER+' al que perteneces.<br />No tiene acceso a esta opcion del sistema.')}
return!1}};Exj.assignStateMnuMain=function(mnuOpcion){var activateModule=!1;var modulesFinish=new Array();var i=-1;modulesFinish[++i]='Clients';modulesFinish[++i]='BillEventos';modulesFinish[++i]='ConfigurarCashier';modulesFinish[++i]='CfgMonedas';modulesFinish[++i]='HelpSysManual';modulesFinish[++i]='InfEventosEPAGOS';modulesFinish[++i]='InfMovDiario';modulesFinish[++i]='InfCountry';modulesFinish[++i]='IdiomaGlobal';modulesFinish[++i]='ImportCotMonAgc';var modulesAgcMain=new Array();modulesAgcMain[++i]='CajaReposition';modulesAgcMain[++i]='EventosSend';var idModule;var _modules=Exj.Global.dataAccess.modules;i=-1;var j=-1;while(++i<modulesFinish.length){idModule=modulesFinish[i];if(idModule==mnuOpcion.id){j=-1;while(++j<_modules.length){if(idModule==_modules[j]){activateModule=!0;break}};break}};if(activateModule){if(Exj.Global.infoUser.is_main_empresa!=1){i=-1;while(++i<modulesAgcMain.length){idModule=modulesAgcMain[i];if(idModule==mnuOpcion.id){activateModule=!1;break}}}};if(Exj.Global.dataAccess.userIsSuperAdmin==1){mnuOpcion.setDisabled(!activateModule)}else{mnuOpcion.setVisible(activateModule)}
return activateModule};Exj.accessModuleAprobe=function(moduleSearch){return!0};Exj.applyAccessTask=function(ctrlButton,taskButton){if(!ctrlButton||!Exj.Global.dataAccess){return}
var _tasks=Exj.Global.dataAccess.tasks;if(!_tasks){return}
if(_tasks.length==0){return}
if(!taskButton){taskButton=''}
var _task_button=taskButton;if((ctrlButton.iconCls=='exj-btn-new')||(ctrlButton.iconCls=='call-center-icon-add')||(ctrlButton.iconCls=='botton-money-add')){_task_button='add'}else if(ctrlButton.iconCls=='exj-btn-edit'){_task_button='edit'}else if((ctrlButton.iconCls=='exj-btn-delete')||(ctrlButton.iconCls=='call-center-icon-del')||(ctrlButton.iconCls=='botton-money-del')){_task_button='publish'}
if(!_task_button){return}
var i=-1;var _task;var _enable=1;while(++i<_tasks.length){_task=_tasks[i];if(_task.aco_value==_task_button){_enable=_task.enable;break}};if(_enable){ctrlButton.setVisible(!0)}else{ctrlButton.setVisible(!1)}};Exj.Criteria=function(configFilter){if(!configFilter){configFilter=new Object();configFilter.fnSearch=''};var btnSearch='';var _pFilters;function newPanelColumn(objPanel){if(objPanel.layout===undefined){objPanel.layout='column'}
objPanel.bodyStyle=Exj.Panel.bodyStyle;objPanel.autoHeight=!0;objPanel.border=!0;if(objPanel.title==undefined){objPanel.title=''}
objPanel.title=Exj.Idioma(objPanel.title);if(objPanel.collapsible==undefined){objPanel.collapsible=!0}
if(objPanel.collapsed==undefined){objPanel.collapsed=!1}
var itemsFilter=objPanel.items;var nCols=itemsFilter.length;var i=-1;while(++i<nCols){_col=itemsFilter[i];_col.autoHeight=!0;_col.style=Exj.Panel.style;_col.border=(objPanel.layout!='column')}
btnSearch=newButtonSearch({text:objPanel.buttonSearchText,tooltip:objPanel.buttonTooltip});_pFilters=new Ext.Panel({title:objPanel.title,bodyStyle:objPanel.bodyStyle,autoHeight:objPanel.autoHeight,layout:objPanel.layout,collapsible:objPanel.collapsible,titleCollapse:!0,collapsed:objPanel.collapsed,border:objPanel.border,items:itemsFilter,buttons:[btnSearch],buttonAlign:'right'});return _pFilters};function newButtonSearch(config){if(config.text==undefined){config.text='Buscar...'}
config.text=Exj.Idioma(config.text);if(config.tooltip==undefined){config.tooltip=''}
var newBtnSearch=Exj.newButton({text:config.text,iconCls:'button-search',tooltip:config.tooltip,handler:function(sender,e){executeSearch()}});if(!btnSearch){btnSearch=newBtnSearch}
return newBtnSearch};function newTextField(config){if(config.width==undefined){config.width='96%'}
if(config.allowBlank==undefined){config.allowBlank=!0}
var _txt=Exj.newTextField({fieldLabel:config.fieldLabel,width:config.width,allowBlank:config.allowBlank,enableKeyEvents:!0});_txt.addListener('specialkey',function(sender,e){if(e.getKey()==e.ENTER){executeSearch(sender.getValue())}});return _txt};function newNumberField(config){if(config.width==undefined){config.width='96%'}
if(config.allowBlank===undefined){config.allowBlank=!0}
config.enableKeyEvents=!0;var _txt=Exj.newNumberField(config);_txt.addListener('specialkey',function(sender,e){if(e.getKey()==e.ENTER){executeSearch(sender.getValue())}});return _txt};function newDateFieldDateTime(config){if(config.allowBlank===undefined){config.allowBlank=!0}
config.enableKeyEvents=!0;var _txt=Exj.newDateFieldDateTime(config);_txt.addListener('specialkey',function(sender,e){if(e.getKey()==e.ENTER){executeSearch(sender.getValue())}});return _txt};function newCheckBox(config){if(config.fieldLabel){config.boxLabel=config.fieldLabel;config.fieldLabel=''}
if(config.value){config.checked=(config.value?!0:!1)}
config.labelSeparator='';var _check=new Ext.form.Checkbox(config);_check.addListener('check',function(sender,checked){executeSearch(checked)});return _check};function newDateFieldDate(config){if(config.allowBlank===undefined){config.allowBlank=!0}
config.enableKeyEvents=!0;var _txt=Exj.newDateFieldDate(config);_txt.addListener('specialkey',function(sender,e){if(e.getKey()==e.ENTER){executeSearch(sender.getValue())}});return _txt};function newPanelFieldDateFromUntil(config){if(config.inColumn===undefined){config.inColumn=!1}
if(config.title==undefined){config.title='DATES'}
if(config.fieldLabelFrom==undefined){config.fieldLabelFrom='Desde'}
if(config.fieldLabelUntil==undefined){config.fieldLabelUntil='Hasta'}
if(config.valueFrom==undefined){config.valueFrom=new Date()}
if(config.valueUntil==undefined){config.valueUntil=new Date()}
var pFD='';var dfFrom=newDateFieldDate({fieldLabel:config.fieldLabelFrom,value:config.valueFrom});var dfUntil=newDateFieldDate({fieldLabel:config.fieldLabelUntil,value:config.valueUntil});if(config.inColumn){pFD=new Ext.Panel({title:config.title,bodyStyle:Exj.bodyStyle,autoHeight:!0,layout:'column',items:[{columnWidth:0.45,autoHeight:!0,border:!1,xtype:'fieldset',labelWidth:45,items:[dfFrom]},{columnWidth:0.55,autoHeight:!0,xtype:'fieldset',labelWidth:45,border:!1,items:[dfUntil]}]})}else{pFD=Exj.newPanel({title:config.title,labelWidth:45,items:[{xtype:'fieldset',labelWidth:45,style:Exj.bodyStyle,border:!1,items:[dfFrom,dfUntil]}]})}
pFD.getValueFrom=function(toServer){if(toServer===undefined){toServer=!0}
var _val=dfFrom.getValue();if(!toServer){return _val}
if(!_val){return''}
_val=Exj.getDateToServer(_val);return _val};pFD.getValueUntil=function(toServer){if(toServer===undefined){toServer=!0}
var _val=dfUntil.getValue();if(!toServer){return _val}
if(!_val){return''}
_val=Exj.getDateToServer(_val);return _val};pFD.setValueFrom=function(valueDate){dfFrom.setValue(valueDate)};pFD.setValueUntil=function(valueDate){dfUntil.setValue(valueDate)};pFD.isValid=function(){if(!dfFrom.isValid()){Exj.mou('The FROM date is not valid.<br />Please review...');dfFrom.focus();return!1}
if(!dfUntil.isValid()){Exj.mou('The UNTIL date is not valid.<br />Please review...');dfUntil.focus();return!1}
return!0};return pFD};function executeSearch(valueSearch){if(configFilter.fnSearch){configFilter.fnSearch(valueSearch)}};return{executeSearch:executeSearch,newPanelColumn:newPanelColumn,newButtonSearch:newButtonSearch,newTextField:newTextField,newDateFieldDateTime:newDateFieldDateTime,newDateFieldDate:newDateFieldDate,newPanelFieldDateFromUntil:newPanelFieldDateFromUntil,newNumberField:newNumberField,newCheckBox:newCheckBox,get_btnSearch:function(){return btnSearch},set_fnSearch:function(fnSearch){configFilter.fnSearch=fnSearch},collapse:function(){if(!_pFilters){return}
_pFilters.collapse()},setDisabled:function(disabled){_pFilters.setDisabled(disabled)}}};Exj.clearDataGrid=function(grid){grid.store.baseParams={};grid.store.removeAll(!1);grid.store.fireEvent('datachanged',grid.store)};Exj.newTreePanel=function(config){var treeP;config=Ext.apply({submit:new Object(),title:'',rootVisible:!1,useArrows:!0,enableDD:!1,root:new Ext.tree.AsyncTreeNode({text:'',draggable:!1,id:'id_tree_App'}),tbar:'',autoHeight:!1,disabled:!1,autoScroll:!0,animate:!0,containerScroll:!0},config);if(config.height==undefined){config.height=0;config.autoHeight=!0}
treeP=new Ext.tree.TreePanel(config);treeP.App=new Object();treeP.Exj.load=function(p){if(!p){p=new Object()}
p=Ext.apply({mask:'Por favor espere...'},p);if(p.url!=undefined){config.submit.url=p.url}
if(p.params!=undefined){config.submit.params=p.params}
if(p.mask!=undefined){config.submit.mask=p.mask}
if(p.success!=undefined){config.submit.success=p.success}
var s=new Exj.submit({url:config.submit.url,params:config.submit.params,idMask:treeP.getEl(),mask:config.submit.mask,showResult:!1,fnSuccess:function(rTree){nodes=rTree.data;var nodeRoot=treeP.getRootNode();var hayMasNodos=!0;while(hayMasNodos){hayMasNodos=!1;nodeRoot.eachChild(function(node){if(node){node.remove()}else{hayMasNodos=!0}})}
nodeRoot.appendChild(nodes);if(config.submit.success){config.submit.success(nodes)}}})};treeP.Exj.reload=function(){if(!config.submit.url){Exj.moe('No se ha definido el parámetro url. Ref. Exj.newTreePanel.');return}
treeP.Exj.load()};treeP.getValue=function(){var _value='';var smTree=treeP.getSelectionModel();var nodeSelTree=smTree.getSelectedNode();if(nodeSelTree){_value=nodeSelTree.id}
return _value};treeP.setValue=function(value,isValueOriginal){if(isValueOriginal===undefined){isValueOriginal=!1}
var node=treeP.getNodeById(value);if(node){if(isValueOriginal){node.originalValue=value}
node.select();return!0}
return!1};return treeP};Exj.mail.showTo=function(cfg){cfg=Ext.apply({attachs:[],scope:null},cfg);if(!cfg.scope){Exj.moe('No se ha definido el alcance del proceso','Presentando correo a enviar');return}
if(!cfg.scope.hUrl){Exj.moe('El alcance del proceso no tiene definido el manejador de URL','Presentando correo a enviar');return}
var infoFilesAttachs=[];var infoNameFileAttach='',nFilesAttach=0;if(cfg.attachs.length){for(var i=0,attach;i<cfg.attachs.length;i++){attach=cfg.attachs[i];if(!attach.dataDownload){continue}
nFilesAttach+=1;var dd=attach.dataDownload;infoFilesAttachs.push(dd.fileName+'.'+dd.fileExt+' (Tamaño: '+dd.fileSize+')');infoNameFileAttach=dd.fileName}}
if(infoFilesAttachs.length>0){infoFilesAttachs=infoFilesAttachs.join(', ')}else{infoFilesAttachs=''}
if(nFilesAttach>1){infoNameFileAttach=nFilesAttach+'Archivos'}
var hURLMailDef=new Exj.HUrl({controller:'mail_defs',option:'com_email_defs'});var _gridTo,_gridDests,_editableModelDests,_editableDests;function _getItemsUIMailDef(editableDef,editableModelDef){var cmbMailTpl=editableDef.getComboBox('id_mail_tpl');var _id_mail_def=editableDef.uie.data.id_mail_def;if(!_id_mail_def){_id_mail_def=0}
var compsUI=new Array();function _onActionAddMailDest(senderButton,e,hUrlList,editableTo,gridListModelTo){_gridDests=Exj.newGridFromEditableModel({editableModel:_editableModelDests,nameList:'mail_defs_dests',scopeModule:cfg.scope,onlyModeLocal:!0});if(_gridDests.store.getCount()<=0){Exj.moi('No existen destinatarios de correo!');return}
var cmbTypeSend=_editableDests.getComboBox('type_send');var winAddDest=Exj.newWindow({title:'Destinatarios disponibles',modal:!0,closable:!0,maximizable:!1,autoHeight:!0,width:Exj.calcWidth(60),buttonAlign:'center',layout:'form',labelWidth:45,buttons:[{text:Exj.Idioma('Adicionar'),iconCls:'app-btn-add',tooltip:'Adiciona el o los destintarios seleccionados',handler:function(){var sm=_gridDests.getSelectionModel();if(sm.getCount()==0){Exj.moi('Debe seleccionar de la lista de Destinatarios');return}
if(!cmbTypeSend.getValue()){Exj.moi('Debe seleccionar el tipo de envio del correo',function(){cmbTypeSend.focus()});return}
var MailToRecord=Ext.data.Record.create(_gridTo.store.fields.items);var recordsTo=new Array();sm.each(function(rDest){var newRecordTo=new MailToRecord({id_mail_def:_id_mail_def,type_send:cmbTypeSend.getValue(),id_sys_user_to_send:rDest.data.id_sys_user,modificado_dt:new Date(),email_to:rDest.data.email_to,nom_empresa:rDest.data.nom_empresa,names_person:rDest.data.names_person});recordsTo.push(newRecordTo)});_gridTo.store.add(recordsTo);winAddDest.destroy()}}],fnCerrar:function(){},items:[_gridDests,cmbTypeSend]});winAddDest.addListener('show',function(senderWin){_gridDests.store.filter([{fn:function(r){var indexFound=_gridTo.store.findBy(function(recordTo,indexTo){return(recordTo.data.email_to==r.data.email_to)});return(indexFound==-1)}}]);if(_gridDests.store.getCount()==0){Exj.moi('No existen destinatarios disponibles, todos fueron asignados como destinatarios del correo.')}});winAddDest.show()};_gridTo=Exj.newGridFromEditableModel({editableModel:editableModelDef,nameList:'mail_defs_to',scopeModule:cfg.scope,onlyModeLocal:!0,onActionNew:_onActionAddMailDest});_editableModelDests=editableModelDef.childsList[0].uiEditable;_editableDests=new Exj.ui.Editable(_editableModelDests);if(infoNameFileAttach&&!editableModelDef.data.id){editableModelDef.data.subject_def+=' '+infoNameFileAttach}
compsUI.push(_gridTo);compsUI.push(editableDef.getTextField('subject_def'));compsUI.push(editableDef.getTextArea('msg_def'));compsUI.push(cmbMailTpl);if(infoFilesAttachs){compsUI.push({xtype:'label',html:'<h3>Archivos adjuntos:</h3>'+infoFilesAttachs})}
return compsUI};Exj.showEditableModel({title:'Envio de Correo',recordEditable:null,idValue:0,hUrl:hURLMailDef,width:Exj.calcWidth(60),labelWidth:60,params:{attachs:cfg.attachs,nam_comp:cfg.scope.hUrl.getOption(),nam_ctll:cfg.scope.hUrl.getController()},fnGetParamsData:function(senderWin,basicForm){return{}},getItemsUI:_getItemsUIMailDef,fnSuccessSave:function(form,result,action){Exj.mail.send({idMail:result.data.idMail,fnSuccess:function(response){Exj.moi('Mail has been sent successfully')}})},fnIsValid:function(){if(_gridTo.store.getCount()<=0){Exj.moi('Debe agregar al menos un destinatario del correo.<br/>Haga clic en el botón <b>Nuevo</b>');return!1}
var msgsError=new Array(),havePara=!1;_gridTo.store.each(function(r){if(!r.data.type_send){msgsError.push('No se ha definido el Tipo en el correo: '+r.data.email_to);return!0}
if(r.data.type_send=='PARA'){havePara=!0}});if(!havePara){msgsError.push('Se debe definido al menos un Tipo <b>PARA</b>')}
if(msgsError.length>0){msgsError=msgsError.join('<br/>');Exj.moi(msgsError);return!1}
return!0},fnSuccess:function(editable,editableModel){},fnBeforeShowWin:function(senderWin){}},cfg.scope)};Exj.getPorcResultSave=function(resultSaveBill){if(!resultSaveBill){return'0%'}
var listProcess=resultSaveBill.listProcess;var i=-1;var nSaved=0;while(++i<listProcess.length){var process=listProcess[i];if(process.saved){++nSaved}}
if(nSaved==0){return'0%'}
return Exj.round((nSaved/listProcess.length)*100,2,!0)+'%'};Exj.getPercent=function(value,nDecimales,color){if(nDecimales==undefined){nDecimales=2}
var per2Dec=Exj.round(value,nDecimales);var per0Dec=Exj.round(value,0);var text='';if(color){text='<span style="color:'+color+';">'}
if(per2Dec==per0Dec){text+=per2Dec}else{text+=Exj.round(value,nDecimales,!0)}
if(color){text+='</span>'}
return text+'%'};Exj.newTextField=function(config){if(config.width===undefined&&!config.anchor){config.width='99%'}
if(config.blankText){config.allowBlank=!1}
if(config.fieldLabel){config.fieldLabel=Exj.Idioma(config.fieldLabel)}
if(config.disabledClass===undefined){config.disabledClass='exj-item-disabled'}
var txt=new Ext.form.TextField(config);return txt};Exj.newTextFieldReadOnly=function(config){config=Ext.apply({cls:'exj-item-readonly',fieldLabel:'',anchor:'99%'},config);config.readOnly=!0;var txfRO=Exj.newTextField(config);txfRO.setReadOnly=function(readOnly){if(this.rendered){this.el.dom.readOnly=readOnly}
this.readOnly=readOnly;if(this.rendered){if(readOnly){this.getActionEl().addClass('exj-item-readonly')}else{this.getActionEl().removeClass('exj-item-readonly')}}};return txfRO};Exj.newTextFieldUpper=function(config){config.cls='exj-text-upper';config.disabledClass='exj-item-disabled';if(config.blankText){config.allowBlank=!1}
var txtUpper=Exj.newTextField(config);return txtUpper};Exj.newWinFixDateTime=function(config){if(config.offset_time==undefined){config.offset_time=0}
var win;var dfTimeServer;var chkEditDateTime;var _exitTimer=!1;var _dateTimeServer='';var _secondsTimer=0;win=new Exj.WinSubmit({title:config.title,iconCls:'button-clock',width:330,height:141,textOk:'Aceptar',textCancel:'Cancelar',fnClose:function(){_exitTimer=!0}},{labelWidth:114});_buildVar();_buildUI();_buildEvents();function _buildVar(){chkEditDateTime=new Ext.form.Checkbox({labelSeparator:'',boxLabel:Exj.Idioma('Editar'),checked:!1});dfTimeServer=Exj.newDateFieldDateTime({fieldLabel:Exj.Idioma('Current Date and Time'),allowBlank:!1,disabled:!0})};function _buildUI(){win.addToForm(chkEditDateTime);win.addToForm(dfTimeServer)};function _buildEvents(){chkEditDateTime.addListener('check',function(sender,checked){dfTimeServer.setDisabled(!checked);if(!checked){getDateTimeFromServer()}});win.setHandlerOk(function(sender,e){if(!chkEditDateTime.getValue()){Exj.mou('No date has been edited, there is nothing to save!');return!1}
if(!win.isValid()){return!1}
Exj.submit({url:'index3.php?option=exj_global&task=fixDateTime',params:{id_pais:config.id_pais,id_empresa:config.id_empresa,date_time:Exj.getDateTimeToServer(dfTimeServer.getValue()),offset_time:config.offset_time},idMask:win.getEl(),mask:'Fijando Fecha y hora...',fnSuccess:function(r){var new_offset_time=r.data.offset_time;if(Exj.Global.infoUser){if(Exj.Global.infoUser.id_pais==config.id_pais){Exj.Global.infoUser.offset_time=new_offset_time;if(config.id_pais){Exj.Global.infoUser.offset_time_cou=new_offset_time}
if(config.id_empresa){Exj.Global.infoUser.offset_time_agc=new_offset_time}}}
win.hide();if(config.fnSaved){config.fnSaved(r)}}});return!0})};function getDateTimeFromServer(){if(chkEditDateTime.getValue()){return}
Exj.submit({url:'index3.php?globals/option=exj_global&task=getDateTime',params:{offset_time:config.offset_time},withoutMask:!0,fnSuccess:function(r){_dateTimeServer=r.data;dfTimeServer.setValue(Exj.getDateTimeFromServer(_dateTimeServer));_secondsTimer=1;timerDateTime()}})};function timerDateTime(miliSecond){if(_exitTimer){return}
if(miliSecond==undefined){miliSecond=1000}
if(chkEditDateTime.getValue()){return}
setTimeout(function(){_secondsTimer+=1;if(!_dateTimeServer){return}
var dtJs=Exj.getDateTimeFromServerForJs(_dateTimeServer);var newTime=new Date(dtJs).add(Date.DAY,0).add(Date.HOUR,0).add(Date.MINUTE,0).add(Date.SECOND,_secondsTimer);newTime=newTime.format(Exj.FormatDateTime);dfTimeServer.setValue(newTime);if(!chkEditDateTime.getValue()){timerDateTime(miliSecond)}},miliSecond)};getDateTimeFromServer();return win};Exj.newFormPanel=function(config){if(config.labelWidth==undefined){config.labelWidth=75}
if(config.frame==undefined){config.frame=!0}
if(config.bodyStyle==undefined){config.bodyStyle=Exj.bodyStyle}
if(config.width==undefined){config.width=360}
var i=-1;while(++i<config.items.length){var item=config.items[i];item.autoHeight=!0}
var fp;fp=new Ext.FormPanel(config);return fp};Exj.getColumnsGrid=function(grid){var cols=new Array();cfgCols=grid.getColumnModel().config;var i=-1;var col;while(++i<cfgCols.length){col=cfgCols[i];var newCol=new Object();newCol.dataIndex=col.dataIndex;newCol.header=col.header;newCol.dataIndex=col.dataIndex;newCol.width=col.width;newCol.hidden=col.hidden;if(newCol.hidden===undefined){newCol.hidden=!1}
cols[i]=newCol};return cols};Exj.configColumns=function(config){config=Ext.apply({xtype:'panel',layout:'column',border:!1,items:[]},config);config.defaults=Ext.apply({layout:'form',border:!1},config.defaults);return config};Exj.clone=function(obj){return eval(uneval(obj))};Exj.cloneSmart=function(obj){if(!obj){return obj}
var newObj=(obj instanceof Array)?[]:{};for(i in obj){if(i=='clone')
continue;if(obj[i]&&typeof obj[i]=="object"){newObj[i]=Exj.cloneSmart(obj[i])}else{newObj[i]=obj[i]}}
return newObj};Exj.isInArrayStrict=function(types,typesToCompare){var numComparations=0;for(var i=0,t;i<types.length;i++){t=types[i];for(var j=0,tc;j<typesToCompare.length;j++){tc=typesToCompare[j];if(t==tc){numComparations+=1}}}
return(numComparations==typesToCompare.length)};Exj.callGeocode=function(params){params=Ext.apply({country:'US',address:'',city_state:'',componentBlock:null,fnSuccess:null,fnFailure:null},params);if(!params.fnSuccess){Exj.moe('No se definió funcion success en geocode');return}
if(params.address){params.address=params.address.trim()}
if(!params.address){return}
if(params.city_state){params.city_state=params.city_state.trim()}
var geocoder=new google.maps.Geocoder();var paramsGeocode=new Object();paramsGeocode.address=params.address;if(params.country){paramsGeocode.region=params.country}
if(params.city_state){if(paramsGeocode.address.toLowerCase().indexOf(params.city_state.toLowerCase())==-1){paramsGeocode.address+=', '+params.city_state}}
var selfFn=this;if(selfFn.isExecutingGeo){return}
if(!selfFn.addToCacheGeo){selfFn.addToCacheGeo=function(paramsAdd){if(!this.cacheGeo){this.cacheGeo=new Array()}
if(paramsAdd.response){paramsAdd.response.inCache=!0;this.cacheGeo.push({address:paramsAdd.address,response:paramsAdd.response})}else if(paramsAdd.failure){paramsAdd.failure.inCache=!0;this.cacheGeo.push({address:paramsAdd.address,failure:paramsAdd.failure})}}}
if(selfFn.cacheGeo){var itemCacheFound=null;for(var indexCache=0,itemCache;indexCache<selfFn.cacheGeo.length;indexCache++){itemCache=selfFn.cacheGeo[indexCache];if(itemCache.address.toLowerCase()==paramsGeocode.address.toLowerCase()){itemCacheFound=itemCache;break}}
if(itemCacheFound){if(itemCacheFound.response){params.fnSuccess(itemCacheFound.response)}else if(itemCacheFound.failure){params.fnFailure(itemCacheFound.failure.msg,itemCacheFound.failure.isFromGoogle,itemCacheFound.failure.inCache)}
return}}else{selfFn.cacheGeo=new Array()}
function _filterResults(results){if(!results||results.length==0){return results}
var itemsFilters=new Array();for(var i=0,resultx;i<results.length;i++){resultx=results[i];if(!resultx.address_components){continue}
var haveCountries=!1;for(var j=0,c;j<resultx.address_components.length;j++){c=resultx.address_components[j];if(Exj.isInArrayStrict(c.types,['country','political'])){haveCountries=!0;if(c.short_name=='US'){itemsFilters.push(resultx);break}}}
if(!haveCountries){itemsFilters.push(resultx)}}
return itemsFilters};selfFn.isExecutingGeo=!0;if(params.componentBlock){params.componentBlock.setDisabled(!0)}
geocoder.geocode(paramsGeocode,function(results,status){selfFn.isExecutingGeo=!1;if(params.componentBlock){params.componentBlock.setDisabled(!1)}
if(status==google.maps.GeocoderStatus.OK){var resultsFilters=_filterResults(results);if(!resultsFilters.length){params.fnFailure('Address returned no results');selfFn.addToCacheGeo({address:paramsGeocode.address,failure:{msg:'Address returned no results'}});return}
var itemResult=resultsFilters[0];if(!itemResult.partial_match){if(results.length>1){params.fnFailure('Address is not accurate');selfFn.addToCacheGeo({address:paramsGeocode.address,failure:{msg:'Address is not accurate'}});return}}
var response=new Object();for(var indexAddress=0,addressComponent;indexAddress<itemResult.address_components.length;indexAddress++){addressComponent=itemResult.address_components[indexAddress];if(!response.postal_code&&Exj.isInArrayStrict(addressComponent.types,['postal_code'])){response.postal_code=addressComponent.short_name}else if(!response.state_code&&Exj.isInArrayStrict(addressComponent.types,['political','administrative_area_level_1'])){response.state_code=addressComponent.short_name}else if(!response.city_name&&Exj.isInArrayStrict(addressComponent.types,['political','neighborhood'])){response.city_name=addressComponent.long_name}else if(!response.street_number&&Exj.isInArrayStrict(addressComponent.types,['street_number'])){response.street_number=addressComponent.short_name}}
response.isFull=!1;if(!response.postal_code&&!response.state_code&&!response.city_name){params.fnFailure('Dirección es incompleta');selfFn.addToCacheGeo({address:paramsGeocode.address,failure:{msg:'Dirección es incompleta'}})}else{if(response.postal_code&&response.state_code&&response.city_name){response.isFull=!0}
response.route_short_name='';if(itemResult.formatted_address){var partesAddress=itemResult.formatted_address.split(',');if(partesAddress.length>=1){response.route_short_name=partesAddress[0].trim()}}
if(!response.route_short_name&&Exj.isInArrayStrict(itemResult.address_components[0].types,['route'])){response.route_short_name=itemResult.address_components[0].short_name}
params.fnSuccess(response);selfFn.addToCacheGeo({address:paramsGeocode.address,response:response})}}else{var msgStatus=status,addInCache=!1;switch(status){case google.maps.GeocoderStatus.ERROR:msgStatus='Error desde Google';break;case google.maps.GeocoderStatus.INVALID_REQUEST:msgStatus='Invalid Request';break;case google.maps.GeocoderStatus.OVER_QUERY_LIMIT:msgStatus='Se exedió el número de consultas';break;case google.maps.GeocoderStatus.REQUEST_DENIED:msgStatus='Request Denied';break;case google.maps.GeocoderStatus.UNKNOWN_ERROR:msgStatus='Unknown Error';break;case google.maps.GeocoderStatus.ZERO_RESULTS:msgStatus='Zero Results';addInCache=!0;break}
var msgFailureGeo='Geocode was not successful for the following reason:<br/>'+msgStatus;params.fnFailure(msgFailureGeo,!0);if(addInCache){selfFn.addToCacheGeo({address:paramsGeocode.address,failure:{msg:msgFailureGeo,isFromGoogle:!0}})}}});return};Exj.syncComboToFields=function(params){params=Ext.apply({combo:null,fields:null,isSetterOriginalValue:!0,fnGetValueField:null},params);if(!params.combo||!params.fields){return!1}
var _fields=new Array();Ext.each(params.fields,function(midexField){if(Ext.isObject(midexField)&&midexField.objField){midexField.objField._nameFieldFromCombo=midexField.nameFieldCombo;_fields.push(midexField.objField)}else{_fields.push(midexField)}});params.combo.addListener('select',function(senderCombo,r,index){Ext.each(_fields,function(field,indexField){var v='',nameFieldCombo=field.name;if(field._nameFieldFromCombo){nameFieldCombo=field._nameFieldFromCombo}
if(params.fnGetValueField&&Ext.isFunction(params.fnGetValueField)){v=params.fnGetValueField(r,nameFieldCombo,field);if(v==undefined){v=r.get(nameFieldCombo)}}else{v=r.get(nameFieldCombo)}
if(v==undefined||v===null){v=''}
field.setValue(v);if(params.isSetterOriginalValue){field.originalValue=v}});if(params.fnAfterSetter&&Ext.isFunction(params.fnAfterSetter)){params.fnAfterSetter(r,senderCombo)}});params.combo.addListener('change',function(senderField,newValue,oldValue){if(!newValue){Ext.each(_fields,function(field,indexField){field.setValue('')})}})};Exj.loadRecordComboFromValue=function(params){params=params||{};var combo=params.combo,value=params.value;if(!combo||!value){Exj.moe('ERROR parámetros en Exj.loadRecordComboFromValue');return}
var isValuesOriginal=params.isValuesOriginal;var enableCombo=(combo.disabled?false:!0);combo.clearValue();if(enableCombo){combo.setDisabled(!0)}
combo.store.load({params:{value:value},add:!0,callback:function(recs,options,success){if(enableCombo){combo.setDisabled(!1)}
if(!success){return}
if(this.find(combo.valueField,value)>=0){combo.setValue(value);if(isValuesOriginal){combo.originalValue=Exj.getValueFromCmp(combo)}}}})};Exj.loadValueCombo=function(params){params=Ext.apply({combo:null,valueComboId:'',elMask:null,isValueOriginal:!0,fnLoadRecord:null,setterValueDataEmpty:!1},params);var combo=params.combo;if(!combo){Exj.moe('No se seteó el parámetro combo en la función: Exj.loadValueCombo');return}
if(!params.valueComboId){if(combo.lazyValue){params.valueComboId=combo.lazyValue}else{return}}
if(combo.lazyValue){combo.lazyValue=null}else{if(combo.getValue()==params.valueComboId){return}
if(combo.isComboSearch&&params.valueComboId){if(combo.getRawValue()==params.valueComboId){return}}}
if(params.elMask){params.elMask.mask('Cargando '+(combo.fieldLabel?combo.fieldLabel:'')+' ...')}
combo.getStore().load({params:{value:params.valueComboId},scopeComboToSetter:combo,callback:function(records,options,success){if(params.elMask){params.elMask.unmask()}
if(!success){return}
if(!records.length){if(params.setterValueDataEmpty&&combo.forceSelection===!1&&params.valueComboId){combo.setValue(params.valueComboId)}
return}
var r=records[0];var valueCombo=r.data.value;if(valueCombo==undefined){valueCombo=r.id}
options.scopeComboToSetter.setValue(valueCombo);if(params.isValueOriginal){options.scopeComboToSetter.originalValue=valueCombo}
if(options.scopeComboToSetter.exjCanFireEventSelect){var rCombo=options.scopeComboToSetter.findRecord(options.scopeComboToSetter.valueField,valueCombo);if(rCombo){options.scopeComboToSetter.fireEvent('select',options.scopeComboToSetter,rCombo,0)}}
if(params.fnLoadRecord){params.fnLoadRecord(r)}}})};Exj.convertDataRawToDataStore=function(topics){return{DataTopics:{topics:topics},status:Exj.Const._EXJ_ESTADO_OK,dataBuffer:'',data:{},Msg:{}}};Exj.bindToContainer=function(container,data,nameCmpFocus,isValuesOriginal){if(!data){return!1}
if(isValuesOriginal===undefined){isValuesOriginal=!1}
if(data instanceof Ext.data.Record){data=data.data}
if(container.fnBindToContainer){container.fnBindToContainer(container,data,nameCmpFocus,isValuesOriginal);return!0}
container.fnBindToContainer=function(senderContainer,senderData,nameCmpFocus,isValuesOriginal){var valueCmp='',loadFromStore=!1,setterValueCmp=!0;for(prop in senderData){var cmp=Exj.getFieldFromName(senderContainer,prop);if(!cmp||!cmp.setValue){continue}
valueCmp=senderData[prop];setterValueCmp=!0;loadFromStore=!1;if(cmp instanceof Ext.form.ComboBox){if(cmp.store){cmp.store.clearFilter()}
if(!valueCmp){if(valueCmp===null){cmp.clearValue();if(isValuesOriginal){cmp.originalValue=""}
continue}else{var rCombo=cmp.findRecord(cmp.valueField,valueCmp);if(!rCombo){cmp.clearValue();if(isValuesOriginal){cmp.originalValue=""}
continue}}}
if(cmp.store&&cmp.store.proxy&&cmp.valueField=='value'&&cmp.autoBindLoad){loadFromStore=!0}
if(!loadFromStore){var rCombo=cmp.findRecord(cmp.valueField,valueCmp);if(!rCombo){setterValueCmp=!1;if(cmp.store&&cmp.store.proxy){Exj.loadRecordComboFromValue({combo:cmp,value:valueCmp,isValuesOriginal:isValuesOriginal})}else{cmp.clearValue();Exj.moe('Error combobox: '+cmp.name+' no existe valor: '+valueCmp+'.<br>Implementar proxy.')}}}}
if(loadFromStore){Exj.loadValueCombo({combo:cmp,valueComboId:valueCmp,elMask:container.getEl(),isValueOriginal:isValuesOriginal,setterValueDataEmpty:!0})}else{if(setterValueCmp){cmp.setValue(valueCmp)}}
if(isValuesOriginal){cmp.originalValue=Exj.getValueFromCmp(cmp)}}
if(nameCmpFocus){var cmp=Exj.getFieldFromName(senderContainer,nameCmpFocus);if(cmp&&cmp.focus&&!cmp.hidden&&!cmp.disabled){cmp.focus(!0,99)}}
if(senderContainer.fnAfterBind){senderContainer.fnAfterBind(senderData)}};if(container instanceof Ext.Window){container.addListener('show',function(senderWin){container.fnBindToContainer(senderWin,data,nameCmpFocus,isValuesOriginal)})}else{container.fnBindToContainer(container,data,nameCmpFocus,isValuesOriginal)}
return!0};Exj.getDataModuleApp=function(idModule){var i=-1;var item;var dataModApp=null;while(++i<Exj.modulesApp.length){item=Exj.modulesApp[i];if(item.id==idModule){dataModApp=item;break}}
return dataModApp};Exj.loadModulesSystem=function(){function _renderModulesApp(items){if(!items){return}
var i=-1;var indexReturn=0;var item;while(++i<items.length){item=items[i];if(item.menu){if(item.menu.items){if(item.menu.items.length){_renderModulesApp(item.menu.items)}}}else{if(item!='-'){if(item.id){var t=new Object();t.id=item.id;t.text=item.text.trim();t.iconCls=item.iconCls;if(!t.iconCls){t.iconCls=''}
indexReturn=Exj.modulesApp.length;Exj.modulesApp[indexReturn]=t}}}}};Exj.modulesApp=null;Exj.modulesApp=new Array();_renderModulesApp(Exj.itemsMenu);return Exj.modulesApp};Exj.getTreeMenuSystem=function(){function renderTree(items,objTree){if(!items){return}
var i=-1;var item;while(++i<items.length){item=items[i];var t=new Object();t.text=item.text;t.iconCls=item.iconCls;if(item.menu){t.children=Exj.cloneSmart(item.menu.items);t.leaf=!1;t.singleClickExpand=!0;t.isClass=!1;objTree[i]=t;if(item.menu.items){renderTree(item.menu.items,t.children)}}else{t.leaf=!0;t.id='APP.'+item.id;t.href='help/app/modules/'+t.id+'.html';t.isClass=!0;if(item=='-'){t.disabled=!0;t.text='--------';t.href='';t.isTarget=!1;t.isClass=!1}
objTree[i]=t}}};function getChildrenTemas(){var dirHelp='help/app/temas/';var nodex=new Object();nodex.id='TratEventos';nodex.text='Tratamiento de Eventos';nodex.iconCls='option';nodex.leaf=!0;nodex.isClass=!0;nodex.href=dirHelp+nodex.id+'.html';var _index=-1;var childrenTemas=new Array();childrenTemas[++_index]=nodex;return childrenTemas};var childenTree=new Array();renderTree(Exj.itemsMenu,childenTree);var obj_pkg=new Object();obj_pkg.id='pkg-APP';obj_pkg.text='APP';obj_pkg.iconCls='icon-pkg';obj_pkg.cls='package';obj_pkg.singleClickExpand=!0;obj_pkg.expanded=!0;obj_pkg.children=childenTree;var obj_temas=new Object();obj_temas.id='temas-APP';obj_temas.text=Exj.Idioma('Temas');obj_temas.iconCls='icon-pkg';obj_temas.cls='package';obj_temas.singleClickExpand=!0;obj_temas.expanded=!0;obj_temas.children=getChildrenTemas();var objTree=new Object();objTree.id="apidocs";objTree.iconCls='icon-docs';objTree.text=Exj.Idioma("General System Help");objTree.singleClickExpand=!0;objTree.children=[obj_pkg,obj_temas];objTree.expanded=!0;objTree.pcount=objTree.children.length;return objTree};Exj.loadStoreFromCombo=function(params){if(!params.objCombo){Exj.moe('ERROR. NO SE ESPECIFICO EL PARAM objCombo EN FUNCION BASE: loadStore');return}
if(!params.params){params.params=new Object()}
if(!params.params.start){params.params.start=0}
if(!params.params.limit){params.params.limit=Exj.LIMIT}
if(!params.baseParams){params.baseParams=params.objCombo.store.baseParams}
if(!params.baseParams){params.baseParams=new Object()}
if(params.objCombo.getValue()){params.objCombo.clearValue()}
if(params.objCombo.store.isFiltered()){params.objCombo.store.clearFilter()}
if(params.baseParams.query){params.baseParams.query=''}
params.objCombo.store.baseParams=params.baseParams;params.objCombo.store.load({params:params.params})};Exj.encodeEspace=function(str,carEncode){if(!str){return str}
if(carEncode==undefined){carEncode='_'}
var strEncode=str;var i=-1;var n=0;while(++i<strEncode.length){if(strEncode[i]==' '){++n}}
i=-1;while(++i<n){strEncode=strEncode.replace(' ',carEncode)}
return strEncode};Exj.getColsFromGrid=function(grid,includeHidden){if(includeHidden===undefined){includeHidden=!1}
var cols=new Array();var cm=grid.getColumnModel();var nCols=cm.getColumnCount();var i=-1;var indexCols=-1;var widthTotal=cm.getTotalWidth(includeHidden);var sumPorc=0;var tt=0;var confCols=cm.config;var confCol;while(++i<nCols){if(!includeHidden){if(cm.isHidden(i)){continue}}
var col=new Object();col.header=Exj.encodeEspace(cm.getColumnHeader(i));col.field=cm.getDataIndex(i);col.width=cm.getColumnWidth(i);confCol=confCols[i];col.align='left';if(!(confCol.align==undefined)){col.align=confCol.align}
if(i==nCols-1){col.widthPorc=Exj.round(100-sumPorc-0.6,2)}else{col.widthPorc=(col.width*100)/widthTotal;col.widthPorc=Exj.round(col.widthPorc,2)}
sumPorc+=col.widthPorc;if(cm.isHidden(i)){col.hidden=!0}else{col.hidden=!1}
cols[++indexCols]=col}
return cols};Exj.in_array=function(elemSearch,arrayX,fieldCompare){if(!arrayX){return!1}
var _index=-1;var found=!1,item;while(++_index<arrayX.length){item=arrayX[_index];if(fieldCompare){if(elemSearch==item[fieldCompare]){found=!0;break}}else{if(elemSearch==item){found=!0;break}}}
return found};Exj.inArray=Exj.in_array;Exj.getItemsFromArray=function(arrayData,idReference,indexFind){var arrayFiltered=new Array();if(indexFind==undefined){indexFind=0}
if(!arrayData){return arrayFiltered}
if(arrayData.length==0){return arrayFiltered}
var i=-1;var itemArray;while(++i<arrayData.length){itemArray=arrayData[i];if(itemArray[indexFind]==idReference){arrayFiltered.push(itemArray)}}
return arrayFiltered};Exj.getItemFromArray=function(arrayData,valueKey,indexKey){var itemArray='';if(indexKey==undefined){indexKey=0}
if(!arrayData){return itemArray}
var i=-1;var subItem;while(++i<arrayData.length){subItem=arrayData[i];if(subItem[indexKey]==valueKey){itemArray=subItem;break}}
return itemArray};Exj.removeItemFromArray=function(arrayData,valueKey,indexKey){if(!arrayData){return arrayData}
itemDelete=Exj.getItemFromArray(arrayData,valueKey,indexKey);if(itemDelete){arrayData.remove(itemDelete)}else{alert('ERROR NO EXISTE EL ELEMENTO EN ARRAY CON KEY: '+valueKey+' indexKey: '+indexKey)}
return arrayData};Exj.setValuesToRecordSelectedGrid=function(grid,valuesFields,saveData){if(saveData===undefined){saveData=!0}
var cm=grid.getSelectionModel();if(!cm){Exj.moe('ERROR EL GRID NO TIENE UN MODELO DE SELECCION');return!1}
var rEdit=cm.getSelected();if(!rEdit){Exj.moe('ERROR NO SE HA SELECCIONADO UN REGISTRO');return!1}
rEdit.beginEdit();var i=-1;var valueField;while(++i<valuesFields.length){valueField=valuesFields[i];rEdit.set(valueField.field,valueField.value)}
rEdit.endEdit();if(saveData){rEdit.commit()}
return!0};Exj.newRadioGroup=function(config){if(config.columns==undefined){config.columns=1}
var i=-1;var item;while(++i<config.items.length){item=config.items[i];if(item.name==undefined&&config.nameItems){item.name=config.nameItems}
var _radio=new Ext.form.Radio(item);if(config.fnCheckedRadio){_radio.addListener('check',function(sender,checked){if(checked){config.fnCheckedRadio(sender,sender.inputValue)}})}
config.items[i]=_radio}
var rg=new Ext.form.RadioGroup(config);rg.addListenerRadios=function(nameEvent,fnCallBack){var radios=rg.items.items;if(!radios){radios=rg.items}
var i=-1;var radio;while(++i<radios.length){radio=radios[i];if(!radio){continue}
radio.addListener(nameEvent,fnCallBack)}};rg.getRadioSelected=function(){var radioSelected='';var radios=rg.items.items;if(!radios){radios=rg.items}
var i=-1;var radio;while(++i<radios.length){radio=radios[i];if(!radio){continue}
if(radio.checked){radioSelected=radio;break}}
return radioSelected};rg.getInputValueSelected=function(){var radio=rg.getRadioSelected();var inputValueSelected='';if(radio){inputValueSelected=radio.inputValue}
return inputValueSelected};rg.setValueRadio=function(inputValue,valueCheck){var radioSelected='';var radios=rg.items.items;if(!radios){radios=rg.items}
var i=-1;var radio;while(++i<radios.length){radio=radios[i];if(!radio){continue}
if(radio.inputValue==inputValue){radio.setValue(valueCheck);break}}};return rg};Exj.newCheckboxGroup=function(config){if(config.columns==undefined){config.columns=1}
var i=-1;var item;while(++i<config.items.length){item=config.items[i];var _checkbox=new Ext.form.Checkbox(item);if(config.fnCheckedCheckbox){_checkbox.addListener('check',function(sender,checked){if(checked){config.fnCheckedCheckbox(sender,sender.inputValue)}})}
config.items[i]=_checkbox}
var rg=new Ext.form.CheckboxGroup(config);rg.addListenerCheckboxs=function(nameEvent,fnCallBack){var checkboxs=rg.items.items;if(!checkboxs){checkboxs=rg.items}
var i=-1;var checkbox;while(++i<checkboxs.length){checkbox=checkboxs[i];if(!checkbox){continue}
checkbox.addListener(nameEvent,fnCallBack)}};rg.getValuesMap=function(){var valuesMap='';var checkboxs=rg.items.items;if(!checkboxs){checkboxs=rg.items}
var i=-1;var checkbox;while(++i<checkboxs.length){checkbox=checkboxs[i];if(!checkbox){continue}
valuesMap+=(checkbox.checked?'1':'0')}
return valuesMap};rg.setValuesMap=function(valuesMap){if(!valuesMap){return}
var checks=rg.items.items;if(!checks){checks=rg.items}
var i=-1;var checkx;var valueMap;while(++i<checks.length){checkx=checks[i];if(!checkx){continue}
valueMap=0;if(i<valuesMap.length){valueMap=valuesMap.substring(i,i+1)}
checkx.setValue((valueMap==1?!0:!1))}};return rg};Exj.newPagingToolbar=function(config){if(!config.pageSize){config.pageSize=Exj.LIMIT}
if(config.emptyMsg==undefined){config.emptyMsg='No data to present'}
config.emptyMsg=Exj.Idioma(config.emptyMsg);config.displayInfo=!0;config.afterPageText=Exj.Idioma('de')+' {0}';config.nextText=Exj.Idioma('Siguiente');config.prevText=Exj.Idioma('Anterior');config.lastText=Exj.Idioma('Ultima Página');config.firstText=Exj.Idioma('Primera Página');config.refreshText=Exj.Idioma('Actualizar');var pagingBar=new Ext.PagingToolbar(config);return pagingBar};Exj.showWinGrid=function(params){if(!params.win){params.win=new Object()}
if(params.title){params.win.title=params.title}
params.win.withTitleExtra=!1;params.win.defaults={msgTarget:'side'};params.win.textCancel=Exj.Idioma('Cerrar');if(params.win.withButtonOk===undefined){params.win.withButtonOk=!1}
params.win.labelWidth=1;if(params.win.width==undefined){params.win.width=Exj.calcWidth(90)}
if(params.win.height==undefined){params.win.height=Exj.calcHeight(90)}
var win=new Exj.WinSubmit(params.win);win.addToForm(params.grid);win.show();if(params.loadGrid){params.loadGrid(win)}else{params.grid.store.load({params:{start:0,limit:Exj.LIMIT}})}};Exj.newCheckBox=function(config){if(config.boxLabel){config.labelSeparator='';config.boxLabel=Exj.Idioma(config.boxLabel)}
if(config.value){config.checked=(config.value?!0:!1)}
var _check=new Ext.form.Checkbox(config);return _check};Exj.implode=function(glue,pieces){var text='';if(!pieces){return text}
if(pieces.length==0){return text}
var i=-1;while(++i<pieces.length){if(text){text+=glue}
text+=pieces[i]}
return text};Exj.explode=function(str,separator,isIntValue){var data=new Array();if(str==null){return data}
if(isIntValue===undefined){isIntValue=!1}
str+='';str=str.trim();if(str.length==0){return data}
if(!separator){separator=','}
var nSep=parseInt(separator.length);var n=str.length;var i=0;var sepStr;var indexData=-1;var lastPos=0;var posFin=0;var itemValue;while(i<n){sepStr=str.substring(i,i+nSep);if(sepStr==separator){posFin=i;itemValue=str.substring(lastPos,posFin).trim();if(isIntValue){if(!itemValue){itemValue=0}
itemValue=parseInt(itemValue)}
data[++indexData]=itemValue;lastPos=i+nSep}
i+=1}
itemValue=str.substring(lastPos);if(isIntValue){if(!itemValue){itemValue=0}
itemValue=parseInt(itemValue)}
data[++indexData]=itemValue;return data};Exj.GridCheckColumn=function(config){Ext.apply(this,config);if(!this.id){this.id=Ext.id()}
this.renderer=this.renderer.createDelegate(this)};Exj.GridCheckColumn.prototype={init:function(grid){this.grid=grid;this.grid.on('render',function(){var view=this.grid.getView();view.mainBody.on('mousedown',this.onMouseDown,this)},this)},onMouseDown:function(e,t){if(t.className&&t.className.indexOf('x-grid3-cc-'+this.id)!=-1){e.stopEvent();var index=this.grid.getView().findRowIndex(t);var record=this.grid.store.getAt(index);record.set(this.dataIndex,!record.data[this.dataIndex])}},renderer:function(v,p,record){p.css+=' x-grid3-check-col-td';return'<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>'}};Exj.evalRenderer=function(items,scopeRender){if(!items){return!1}
if(!Ext.isArray(items)){return!1}
for(var i=0,col;i<items.length;i++){col=items[i];if(col.renderer&&Ext.isString(col.renderer)){if(Exj.isRenderBase(col.renderer)){col.renderer=eval(col.renderer)}else{if(scopeRender){col.renderer=scopeRender[col.renderer]}}}
if(col.getClass&&Ext.isString(col.getClass)){var strGetClass=col.getClass;if(Exj.isRenderBase(strGetClass)){col.getClass=eval(col.getClass)}else{if(scopeRender){col.getClass=scopeRender[strGetClass];if(col.getClass===undefined){alert('ERROR getClass. No se definió la función: '+strGetClass+' en scope. Col: '+col.dataIndex)}}}}
if(col.handler&&Ext.isString(col.handler)){var strHandler=col.handler;if(Exj.isRenderBase(strHandler)){col.handler=eval(col.handler)}else{if(scopeRender){col.handler=scopeRender[strHandler];if(col.handler===undefined){alert('ERROR handler. No se definió la función: '+strHandler+' en scope. Col: '+col.dataIndex)}}}}
if(col.beforeCheckColumn&&Ext.isString(col.beforeCheckColumn)){var strBeforeCheckColumn=col.beforeCheckColumn;if(Exj.isRenderBase(strBeforeCheckColumn)){col.beforeCheckColumn=eval(col.beforeCheckColumn)}else{if(scopeRender){col.beforeCheckColumn=scopeRender[strBeforeCheckColumn];if(col.beforeCheckColumn===undefined){alert('ERROR beforeCheckColumn. No se definió la función: '+strBeforeCheckColumn+' en scope. Col: '+col.dataIndex)}}}}}
return!0};Exj.renderColCkeck=function(value,valueText){if(value==='0'){value=0}
if(valueText===undefined||valueText===''){valueText='&#160;'}
var cssCheck=(value?'x-grid3-check-col-on':'x-grid3-check-col');return('<div class='+cssCheck+'>'+valueText+'</div>')};Exj.evalFields=function(fields,scopeRender){if(!fields||!scopeRender){return!1}
for(var i=0,f;i<fields.length;i++){f=fields[i];if(!f.convert){continue}
f.convert=scopeRender[f.convert]}
return!0};Exj.evalRendererListModel=function(listModel,scopeRender){if(!listModel){return!1}
if(listModel.listsSecModels&&Ext.isArray(listModel.listsSecModels)){for(var i=0,itemSecModel;i<listModel.listsSecModels.length;i++){itemSecModel=listModel.listsSecModels[i];Exj.evalRendererListModel(itemSecModel.listModel,scopeRender)}}
var items=null;if(listModel.cfgGrid&&listModel.cfgGrid.columns){items=listModel.cfgGrid.columns}else{items=listModel.columns}
if(listModel.cfgStore&&listModel.cfgStore.fields){Exj.evalFields(listModel.cfgStore.fields,scopeRender)}
return Exj.evalRenderer(items,scopeRender)};Exj.newGridColumnModel=function(arrayCols,sm){if(sm==undefined){sm=''}
var i=-1;var nCols=arrayCols.length;var col;while(++i<nCols){col=arrayCols[i];if(col.renderer==undefined){col.renderer=Exj.rendererText}
if(col.sortable===undefined){col.sortable=!0}}
if(sm){var newArrayCols=new Array();i=-1;var newIndex=-1;newArrayCols[++newIndex]=sm;while(++i<nCols){col=arrayCols[i];newArrayCols[++newIndex]=col}
arrayCols=newArrayCols}
var _cm=new Exj.ui.GridColumnModel(arrayCols);_cm.fixWidthCols=function(totalWidth,offsetWidth){if(offsetWidth===undefined){offsetWidth=!0}
if(totalWidth==undefined){totalWidth=Exj.calcGridWidth()}else{if(offsetWidth){totalWidth-=48}}
i=-1;var wcol;var twcols=_cm.getTotalWidth(!1);var pCol;while(++i<nCols){wcol=_cm.getColumnWidth(i);pCol=(wcol*100)/twcols;wcol=Exj.round((pCol*totalWidth)/100,2);_cm.setColumnWidth(i,wcol)}};return _cm};Exj.newGridPanel=function(config){var grid;if(!config.titleModule){config.titleModule='Items'}
config.titleModule=Exj.Idioma(config.titleModule);config.stripeRows=!0;if(!config.loadMaskMsg){config.loadMaskMsg=Exj.Idioma('Obteniendo Lista de')+': '+config.titleModule}
config.cm.defaultSortable=!0;if(config.fnRowSelect){if(!config.sm){config.sm=new Ext.grid.RowSelectionModel({singleSelect:!0})}
config.sm.addListener('rowselect',function(sender,index,r){config.fnRowSelect(sender,index,r)})}
config.bbar=Exj.newPagingToolbar({store:config.store,displayMsg:config.titleModule+' {0} - {1} '+Exj.Idioma('de')+' {2}',emptyMsg:Exj.Idioma('No hay')+' '+config.titleModule});if(config.width==undefined){config.width=Exj.calcGridWidth()-config.cm.getColumnCount()}
if(config.cm.fixWidthCols){config.cm.fixWidthCols(config.width-6,!1)}
grid=new Exj.ui.GridPanel(config);return grid};Exj.calcGridWidth=function(pWidth){width=Exj.calcWidth(pWidth);width-=48;return width};Exj.parseIntValue=function(value){if(!value){value=0}
value=parseInt(value);return value};Exj.pg.getParams=function(){return Exj.Global.infoUser.paramsGen};Exj.pg.setParams=function(pg){if(!pg){return}
Exj.Global.infoUser.paramsGen=pg};Exj._buildLoadMask=function(msgDefault){var _msg=Exj.Idioma('Cargando');if(msgDefault){_msg+=': '+msgDefault}
_msg+='...';return{msg:_msg}};Exj.HUrl=function(cfgURL){cfgURL=Ext.apply({controller:'',option:'app_nodefinido'},cfgURL);var me=this;var _urlBase='index3.php';function _buildURLWithModel(model,optionCustom){var url=_urlBase;if(cfgURL.controller){url+='/'+cfgURL.controller}
if(model){url+='/'+model}
if(cfgURL.id!==undefined){url+='/'+cfgURL.id}
url+='?option=';if(optionCustom){url+=optionCustom}else{url+=cfgURL.option}
url=Exj.addParamsURL(url);return url};this.getOption=function(){return cfgURL.option};this.setOption=function(newOption){cfgURL.option=newOption;return me};this.getActionDownloadPDF=function(params){params=params||{};params.isRestFul=!1;return Exj.addParamsHref(_buildURLWithModel('downloadPDF'),params)};this.getActionDownloadEXCELXLS=function(params){params=params||{};params.isRestFul=!1;return Exj.addParamsHref(_buildURLWithModel('downloadEXCELXLS'),params)};this.getActionDownloadEXCELXLSX=function(params){params=params||{};params.isRestFul=!1;return Exj.addParamsHref(_buildURLWithModel('downloadEXCELXLSX'),params)};this.getActionDownloadFile=function(params){params=params||{};params.isRestFul=!1;return Exj.addParamsHref(_buildURLWithModel('DownloadFile'),params)};this.getActionHelpViewCmp=function(params,hURLHelp){params=params||{};params.isRestFul=!1;if(!params.format){params.format='htmlx'}
if(!params.nameCmp){params.nameCmp=this.getOption()}
if(!hURLHelp){hURLHelp=new Exj.HUrl({controller:'helps',option:'exj_app_help'})}
return Exj.addParamsHref(hURLHelp.getActionView('viewCmp'),params)};this.getActionCustom=function(action){return _buildURLWithModel(action)};this.getActionView=function(action){action=action||'view';return _buildURLWithModel(action)};this.getActionCreate=function(){return _buildURLWithModel('create')};this.getActionUpdate=function(){return _buildURLWithModel('update')};this.getActionDestroy=function(){return _buildURLWithModel('destroy')};this.getActionViewModel=function(){return _buildURLWithModel('viewModel')};this.getActionListModel=function(){return _buildURLWithModel('listModel')};this.getActionReportModel=function(){return _buildURLWithModel('reportModel')};this.getActionReportHTML=function(){return _buildURLWithModel('reportHTML')};this.getActionImportModel=function(){return _buildURLWithModel('importModel')};this.getActionUploadFile=function(){return _buildURLWithModel('uploadFile')};this.getActionEditableModel=function(){return _buildURLWithModel('editableModel')};this.getActionDeleteImportGetItems=function(){return _buildURLWithModel('deleteImportGetItems')};this.getActionDeleteImportConfirmed=function(){return _buildURLWithModel('deleteImportConfirmed')};this.getApiProxyDefault=function(){return{read:me.getActionView(),create:me.getActionCreate(),update:me.getActionUpdate(),destroy:me.getActionDestroy()}};this.getUrlFromMethod=function(method){if(method=='POST'){return me.getActionCreate()}
if(method=='GET'){return me.getActionView()}
if(method=='PUT'){return me.getActionUpdate()}
if(method=='DESTROY'||method=='DELETE'){return me.getActionDestroy()}
if(!method){return me.getActionView()}};this.setController=function(controller){cfgURL.controller=controller;return me};this.getController=function(controller){return cfgURL.controller};this.setId=function(id){cfgURL.id=id;return me};this.getId=function(){return cfgURL.id};return this}