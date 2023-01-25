function isCompatible(ua){return!!((function(){'use strict';return!this&&Function.prototype.bind;}())&&'querySelector'in document&&'localStorage'in window&&!ua.match(/MSIE 10|NetFront|Opera Mini|S40OviBrowser|MeeGo|Android.+Glass|^Mozilla\/5\.0 .+ Gecko\/$|googleweblight|PLAYSTATION|PlayStation/));}if(!isCompatible(navigator.userAgent)){document.documentElement.className=document.documentElement.className.replace(/(^|\s)client-js(\s|$)/,'$1client-nojs$2');while(window.NORLQ&&NORLQ[0]){NORLQ.shift()();}NORLQ={push:function(fn){fn();}};RLQ={push:function(){}};}else{if(window.performance&&performance.mark){performance.mark('mwStartup');}(function(){'use strict';var mw,log,con=window.console;function logError(topic,data){var msg,e=data.exception;if(con.log){msg=(e?'Exception':'Error')+' in '+data.source+(data.module?' in module '+data.module:'')+(e?':':'.');con.log(msg);if(e&&con.warn){con.warn(e);}}}function Map(){this.values=Object.create(null);}Map.prototype={constructor:Map,get:
function(selection,fallback){var results,i;fallback=arguments.length>1?fallback:null;if(Array.isArray(selection)){results={};for(i=0;i<selection.length;i++){if(typeof selection[i]==='string'){results[selection[i]]=selection[i]in this.values?this.values[selection[i]]:fallback;}}return results;}if(typeof selection==='string'){return selection in this.values?this.values[selection]:fallback;}if(selection===undefined){results={};for(i in this.values){results[i]=this.values[i];}return results;}return fallback;},set:function(selection,value){if(arguments.length>1){if(typeof selection==='string'){this.values[selection]=value;return true;}}else if(typeof selection==='object'){for(var s in selection){this.values[s]=selection[s];}return true;}return false;},exists:function(selection){return typeof selection==='string'&&selection in this.values;}};log=function(){};log.warn=con.warn?Function.prototype.bind.call(con.warn,con):function(){};mw={now:function(){var perf=window.performance,navStart=perf
&&perf.timing&&perf.timing.navigationStart;mw.now=navStart&&perf.now?function(){return navStart+perf.now();}:Date.now;return mw.now();},trackQueue:[],track:function(topic,data){mw.trackQueue.push({topic:topic,data:data});},trackError:function(topic,data){mw.track(topic,data);logError(topic,data);},Map:Map,config:new Map(),messages:new Map(),templates:new Map(),log:log};window.mw=window.mediaWiki=mw;}());(function(){'use strict';var StringSet,store,hasOwn=Object.hasOwnProperty;function defineFallbacks(){StringSet=window.Set||function(){var set=Object.create(null);return{add:function(value){set[value]=true;},has:function(value){return value in set;}};};}defineFallbacks();function fnv132(str){var hash=0x811C9DC5;for(var i=0;i<str.length;i++){hash+=(hash<<1)+(hash<<4)+(hash<<7)+(hash<<8)+(hash<<24);hash^=str.charCodeAt(i);}hash=(hash>>>0).toString(36).slice(0,5);while(hash.length<5){hash='0'+hash;}return hash;}var isES6Supported=typeof Promise==='function'&&Promise.prototype.finally&&/./g.
flags==='g'&&(function(){try{new Function('(a = 0) => a');return true;}catch(e){return false;}}());var registry=Object.create(null),sources=Object.create(null),handlingPendingRequests=false,pendingRequests=[],queue=[],jobs=[],willPropagate=false,errorModules=[],baseModules=["jquery","mediawiki.base"],marker=document.querySelector('meta[name="ResourceLoaderDynamicStyles"]'),lastCssBuffer,rAF=window.requestAnimationFrame||setTimeout;function newStyleTag(text,nextNode){var el=document.createElement('style');el.appendChild(document.createTextNode(text));if(nextNode&&nextNode.parentNode){nextNode.parentNode.insertBefore(el,nextNode);}else{document.head.appendChild(el);}return el;}function flushCssBuffer(cssBuffer){if(cssBuffer===lastCssBuffer){lastCssBuffer=null;}newStyleTag(cssBuffer.cssText,marker);for(var i=0;i<cssBuffer.callbacks.length;i++){cssBuffer.callbacks[i]();}}function addEmbeddedCSS(cssText,callback){if(!lastCssBuffer||cssText.slice(0,7)==='@import'){lastCssBuffer={cssText:'',
callbacks:[]};rAF(flushCssBuffer.bind(null,lastCssBuffer));}lastCssBuffer.cssText+='\n'+cssText;lastCssBuffer.callbacks.push(callback);}function getCombinedVersion(modules){var hashes=modules.reduce(function(result,module){return result+registry[module].version;},'');return fnv132(hashes);}function allReady(modules){for(var i=0;i<modules.length;i++){if(mw.loader.getState(modules[i])!=='ready'){return false;}}return true;}function allWithImplicitReady(module){return allReady(registry[module].dependencies)&&(baseModules.indexOf(module)!==-1||allReady(baseModules));}function anyFailed(modules){for(var i=0;i<modules.length;i++){var state=mw.loader.getState(modules[i]);if(state==='error'||state==='missing'){return modules[i];}}return false;}function doPropagation(){var didPropagate=true;var module;while(didPropagate){didPropagate=false;while(errorModules.length){var errorModule=errorModules.shift(),baseModuleError=baseModules.indexOf(errorModule)!==-1;for(module in registry){if(registry[
module].state!=='error'&&registry[module].state!=='missing'){if(baseModuleError&&baseModules.indexOf(module)===-1){registry[module].state='error';didPropagate=true;}else if(registry[module].dependencies.indexOf(errorModule)!==-1){registry[module].state='error';errorModules.push(module);didPropagate=true;}}}}for(module in registry){if(registry[module].state==='loaded'&&allWithImplicitReady(module)){execute(module);didPropagate=true;}}for(var i=0;i<jobs.length;i++){var job=jobs[i];var failed=anyFailed(job.dependencies);if(failed!==false||allReady(job.dependencies)){jobs.splice(i,1);i-=1;try{if(failed!==false&&job.error){job.error(new Error('Failed dependency: '+failed),job.dependencies);}else if(failed===false&&job.ready){job.ready();}}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'load-callback'});}didPropagate=true;}}}willPropagate=false;}function setAndPropagate(module,state){registry[module].state=state;if(state==='ready'){store.add(module);}else if(state===
'error'||state==='missing'){errorModules.push(module);}else if(state!=='loaded'){return;}if(willPropagate){return;}willPropagate=true;mw.requestIdleCallback(doPropagation,{timeout:1});}function sortDependencies(module,resolved,unresolved){if(!(module in registry)){throw new Error('Unknown module: '+module);}if(typeof registry[module].skip==='string'){var skip=(new Function(registry[module].skip)());registry[module].skip=!!skip;if(skip){registry[module].dependencies=[];setAndPropagate(module,'ready');return;}}if(!unresolved){unresolved=new StringSet();}var deps=registry[module].dependencies;unresolved.add(module);for(var i=0;i<deps.length;i++){if(resolved.indexOf(deps[i])===-1){if(unresolved.has(deps[i])){throw new Error('Circular reference detected: '+module+' -> '+deps[i]);}sortDependencies(deps[i],resolved,unresolved);}}resolved.push(module);}function resolve(modules){var resolved=baseModules.slice();for(var i=0;i<modules.length;i++){sortDependencies(modules[i],resolved);}return resolved
;}function resolveStubbornly(modules){var resolved=baseModules.slice();for(var i=0;i<modules.length;i++){var saved=resolved.slice();try{sortDependencies(modules[i],resolved);}catch(err){resolved=saved;mw.log.warn('Skipped unavailable module '+modules[i]);if(modules[i]in registry){mw.trackError('resourceloader.exception',{exception:err,source:'resolve'});}}}return resolved;}function resolveRelativePath(relativePath,basePath){var relParts=relativePath.match(/^((?:\.\.?\/)+)(.*)$/);if(!relParts){return null;}var baseDirParts=basePath.split('/');baseDirParts.pop();var prefixes=relParts[1].split('/');prefixes.pop();var prefix;while((prefix=prefixes.pop())!==undefined){if(prefix==='..'){baseDirParts.pop();}}return(baseDirParts.length?baseDirParts.join('/')+'/':'')+relParts[2];}function makeRequireFunction(moduleObj,basePath){return function require(moduleName){var fileName=resolveRelativePath(moduleName,basePath);if(fileName===null){return mw.loader.require(moduleName);}if(hasOwn.call(
moduleObj.packageExports,fileName)){return moduleObj.packageExports[fileName];}var scriptFiles=moduleObj.script.files;if(!hasOwn.call(scriptFiles,fileName)){throw new Error('Cannot require undefined file '+fileName);}var result,fileContent=scriptFiles[fileName];if(typeof fileContent==='function'){var moduleParam={exports:{}};fileContent(makeRequireFunction(moduleObj,fileName),moduleParam,moduleParam.exports);result=moduleParam.exports;}else{result=fileContent;}moduleObj.packageExports[fileName]=result;return result;};}function addScript(src,callback){var script=document.createElement('script');script.src=src;script.onload=script.onerror=function(){if(script.parentNode){script.parentNode.removeChild(script);}if(callback){callback();callback=null;}};document.head.appendChild(script);}function queueModuleScript(src,moduleName,callback){pendingRequests.push(function(){if(moduleName!=='jquery'){window.require=mw.loader.require;window.module=registry[moduleName].module;}addScript(src,
function(){delete window.module;callback();if(pendingRequests[0]){pendingRequests.shift()();}else{handlingPendingRequests=false;}});});if(!handlingPendingRequests&&pendingRequests[0]){handlingPendingRequests=true;pendingRequests.shift()();}}function addLink(url,media,nextNode){var el=document.createElement('link');el.rel='stylesheet';if(media){el.media=media;}el.href=url;if(nextNode&&nextNode.parentNode){nextNode.parentNode.insertBefore(el,nextNode);}else{document.head.appendChild(el);}}function domEval(code){var script=document.createElement('script');if(mw.config.get('wgCSPNonce')!==false){script.nonce=mw.config.get('wgCSPNonce');}script.text=code;document.head.appendChild(script);script.parentNode.removeChild(script);}function enqueue(dependencies,ready,error){if(allReady(dependencies)){if(ready){ready();}return;}var failed=anyFailed(dependencies);if(failed!==false){if(error){error(new Error('Dependency '+failed+' failed to load'),dependencies);}return;}if(ready||error){jobs.push({
dependencies:dependencies.filter(function(module){var state=registry[module].state;return state==='registered'||state==='loaded'||state==='loading'||state==='executing';}),ready:ready,error:error});}dependencies.forEach(function(module){if(registry[module].state==='registered'&&queue.indexOf(module)===-1){queue.push(module);}});mw.loader.work();}function execute(module){if(registry[module].state!=='loaded'){throw new Error('Module in state "'+registry[module].state+'" may not execute: '+module);}registry[module].state='executing';var runScript=function(){var script=registry[module].script;var markModuleReady=function(){setAndPropagate(module,'ready');};var nestedAddScript=function(arr,offset){if(offset>=arr.length){markModuleReady();return;}queueModuleScript(arr[offset],module,function(){nestedAddScript(arr,offset+1);});};try{if(Array.isArray(script)){nestedAddScript(script,0);}else if(typeof script==='function'){if(module==='jquery'){script();}else{script(window.$,window.$,mw.loader.
require,registry[module].module);}markModuleReady();}else if(typeof script==='object'&&script!==null){var mainScript=script.files[script.main];if(typeof mainScript!=='function'){throw new Error('Main file in module '+module+' must be a function');}mainScript(makeRequireFunction(registry[module],script.main),registry[module].module,registry[module].module.exports);markModuleReady();}else if(typeof script==='string'){domEval(script);markModuleReady();}else{markModuleReady();}}catch(e){setAndPropagate(module,'error');mw.trackError('resourceloader.exception',{exception:e,module:module,source:'module-execute'});}};if(registry[module].messages){mw.messages.set(registry[module].messages);}if(registry[module].templates){mw.templates.set(module,registry[module].templates);}var cssPending=0;var cssHandle=function(){cssPending++;return function(){cssPending--;if(cssPending===0){var runScriptCopy=runScript;runScript=undefined;runScriptCopy();}};};if(registry[module].style){for(var key in registry[
module].style){var value=registry[module].style[key];if(key==='css'){for(var i=0;i<value.length;i++){addEmbeddedCSS(value[i],cssHandle());}}else if(key==='url'){for(var media in value){var urls=value[media];for(var j=0;j<urls.length;j++){addLink(urls[j],media,marker);}}}}}if(module==='user'){var siteDeps;var siteDepErr;try{siteDeps=resolve(['site']);}catch(e){siteDepErr=e;runScript();}if(!siteDepErr){enqueue(siteDeps,runScript,runScript);}}else if(cssPending===0){runScript();}}function sortQuery(o){var sorted={};var list=[];for(var key in o){list.push(key);}list.sort();for(var i=0;i<list.length;i++){sorted[list[i]]=o[list[i]];}return sorted;}function buildModulesString(moduleMap){var str=[];var list=[];var p;function restore(suffix){return p+suffix;}for(var prefix in moduleMap){p=prefix===''?'':prefix+'.';str.push(p+moduleMap[prefix].join(','));list.push.apply(list,moduleMap[prefix].map(restore));}return{str:str.join('|'),list:list};}function makeQueryString(params){var chunks=[];for(
var key in params){chunks.push(encodeURIComponent(key)+'='+encodeURIComponent(params[key]));}return chunks.join('&');}function batchRequest(batch){if(!batch.length){return;}var sourceLoadScript,currReqBase,moduleMap;function doRequest(){var query=Object.create(currReqBase),packed=buildModulesString(moduleMap);query.modules=packed.str;query.version=getCombinedVersion(packed.list);query=sortQuery(query);addScript(sourceLoadScript+'?'+makeQueryString(query));}batch.sort();var reqBase={"lang":"ko","skin":"minerva"};var splits=Object.create(null);for(var b=0;b<batch.length;b++){var bSource=registry[batch[b]].source;var bGroup=registry[batch[b]].group;if(!splits[bSource]){splits[bSource]=Object.create(null);}if(!splits[bSource][bGroup]){splits[bSource][bGroup]=[];}splits[bSource][bGroup].push(batch[b]);}for(var source in splits){sourceLoadScript=sources[source];for(var group in splits[source]){var modules=splits[source][group];currReqBase=Object.create(reqBase);if(group===0&&mw.config.get(
'wgUserName')!==null){currReqBase.user=mw.config.get('wgUserName');}var currReqBaseLength=makeQueryString(currReqBase).length+23;var length=currReqBaseLength;var currReqModules=[];moduleMap=Object.create(null);for(var i=0;i<modules.length;i++){var lastDotIndex=modules[i].lastIndexOf('.'),prefix=modules[i].slice(0,Math.max(0,lastDotIndex)),suffix=modules[i].slice(lastDotIndex+1),bytesAdded=moduleMap[prefix]?suffix.length+3:modules[i].length+3;if(currReqModules.length&&length+bytesAdded>mw.loader.maxQueryLength){doRequest();length=currReqBaseLength;moduleMap=Object.create(null);currReqModules=[];}if(!moduleMap[prefix]){moduleMap[prefix]=[];}length+=bytesAdded;moduleMap[prefix].push(suffix);currReqModules.push(modules[i]);}if(currReqModules.length){doRequest();}}}}function asyncEval(implementations,cb){if(!implementations.length){return;}mw.requestIdleCallback(function(){try{domEval(implementations.join(';'));}catch(err){cb(err);}});}function getModuleKey(module){return module in registry
?(module+'@'+registry[module].version):null;}function splitModuleKey(key){var index=key.lastIndexOf('@');if(index===-1||index===0){return{name:key,version:''};}return{name:key.slice(0,index),version:key.slice(index+1)};}function registerOne(module,version,dependencies,group,source,skip){if(module in registry){throw new Error('module already registered: '+module);}version=String(version||'');if(version.slice(-1)==='!'){if(!isES6Supported){return;}version=version.slice(0,-1);}registry[module]={module:{exports:{}},packageExports:{},version:version,dependencies:dependencies||[],group:typeof group==='undefined'?null:group,source:typeof source==='string'?source:'local',state:'registered',skip:typeof skip==='string'?skip:null};}mw.loader={moduleRegistry:registry,maxQueryLength:2000,addStyleTag:newStyleTag,enqueue:enqueue,resolve:resolve,work:function(){store.init();var q=queue.length,storedImplementations=[],storedNames=[],requestNames=[],batch=new StringSet();while(q--){var module=queue[q];
if(mw.loader.getState(module)==='registered'&&!batch.has(module)){registry[module].state='loading';batch.add(module);var implementation=store.get(module);if(implementation){storedImplementations.push(implementation);storedNames.push(module);}else{requestNames.push(module);}}}queue=[];asyncEval(storedImplementations,function(err){store.stats.failed++;store.clear();mw.trackError('resourceloader.exception',{exception:err,source:'store-eval'});var failed=storedNames.filter(function(name){return registry[name].state==='loading';});batchRequest(failed);});batchRequest(requestNames);},addSource:function(ids){for(var id in ids){if(id in sources){throw new Error('source already registered: '+id);}sources[id]=ids[id];}},register:function(modules){if(typeof modules!=='object'){registerOne.apply(null,arguments);return;}function resolveIndex(dep){return typeof dep==='number'?modules[dep][0]:dep;}for(var i=0;i<modules.length;i++){var deps=modules[i][2];if(deps){for(var j=0;j<deps.length;j++){deps[j]
=resolveIndex(deps[j]);}}registerOne.apply(null,modules[i]);}},implement:function(module,script,style,messages,templates){var split=splitModuleKey(module),name=split.name,version=split.version;if(!(name in registry)){mw.loader.register(name);}if(registry[name].script!==undefined){throw new Error('module already implemented: '+name);}if(version){registry[name].version=version;}registry[name].script=script||null;registry[name].style=style||null;registry[name].messages=messages||null;registry[name].templates=templates||null;if(registry[name].state!=='error'&&registry[name].state!=='missing'){setAndPropagate(name,'loaded');}},load:function(modules,type){if(typeof modules==='string'&&/^(https?:)?\/?\//.test(modules)){if(type==='text/css'){addLink(modules);}else if(type==='text/javascript'||type===undefined){addScript(modules);}else{throw new Error('Invalid type '+type);}}else{modules=typeof modules==='string'?[modules]:modules;enqueue(resolveStubbornly(modules));}},state:function(states){
for(var module in states){if(!(module in registry)){mw.loader.register(module);}setAndPropagate(module,states[module]);}},getState:function(module){return module in registry?registry[module].state:null;},require:function(moduleName){if(mw.loader.getState(moduleName)!=='ready'){throw new Error('Module "'+moduleName+'" is not loaded');}return registry[moduleName].module.exports;}};var hasPendingWrites=false;function flushWrites(){store.prune();while(store.queue.length){store.set(store.queue.shift());}try{localStorage.removeItem(store.key);var data=JSON.stringify(store);localStorage.setItem(store.key,data);}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'store-localstorage-update'});}hasPendingWrites=false;}mw.loader.store=store={enabled:null,items:{},queue:[],stats:{hits:0,misses:0,expired:0,failed:0},toJSON:function(){return{items:store.items,vary:store.vary,asOf:Math.ceil(Date.now()/1e7)};},key:"MediaWikiModuleStore:mediawiki",vary:"minerva:1:ko",init:function(){
if(this.enabled===null){this.enabled=false;if(false||/Firefox/.test(navigator.userAgent)){this.clear();}else{this.load();}}},load:function(){try{var raw=localStorage.getItem(this.key);this.enabled=true;var data=JSON.parse(raw);if(data&&data.vary===this.vary&&data.items&&Date.now()<(data.asOf*1e7)+259e7){this.items=data.items;}}catch(e){}},get:function(module){if(this.enabled){var key=getModuleKey(module);if(key in this.items){this.stats.hits++;return this.items[key];}this.stats.misses++;}return false;},add:function(module){if(this.enabled){this.queue.push(module);this.requestUpdate();}},set:function(module){var args,encodedScript,descriptor=registry[module],key=getModuleKey(module);if(key in this.items||!descriptor||descriptor.state!=='ready'||!descriptor.version||descriptor.group===1||descriptor.group===0||[descriptor.script,descriptor.style,descriptor.messages,descriptor.templates].indexOf(undefined)!==-1){return;}try{if(typeof descriptor.script==='function'){encodedScript=String(
descriptor.script);}else if(typeof descriptor.script==='object'&&descriptor.script&&!Array.isArray(descriptor.script)){encodedScript='{'+'main:'+JSON.stringify(descriptor.script.main)+','+'files:{'+Object.keys(descriptor.script.files).map(function(file){var value=descriptor.script.files[file];return JSON.stringify(file)+':'+(typeof value==='function'?value:JSON.stringify(value));}).join(',')+'}}';}else{encodedScript=JSON.stringify(descriptor.script);}args=[JSON.stringify(key),encodedScript,JSON.stringify(descriptor.style),JSON.stringify(descriptor.messages),JSON.stringify(descriptor.templates)];}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'store-localstorage-json'});return;}var src='mw.loader.implement('+args.join(',')+');';if(src.length>1e5){return;}this.items[key]=src;},prune:function(){for(var key in this.items){if(getModuleKey(splitModuleKey(key).name)!==key){this.stats.expired++;delete this.items[key];}}},clear:function(){this.items={};try{localStorage.
removeItem(this.key);}catch(e){}},requestUpdate:function(){if(!hasPendingWrites){hasPendingWrites=true;setTimeout(function(){mw.requestIdleCallback(flushWrites);},2000);}}};}());mw.requestIdleCallbackInternal=function(callback){setTimeout(function(){var start=mw.now();callback({didTimeout:false,timeRemaining:function(){return Math.max(0,50-(mw.now()-start));}});},1);};mw.requestIdleCallback=window.requestIdleCallback?window.requestIdleCallback.bind(window):mw.requestIdleCallbackInternal;(function(){var queue;mw.loader.addSource({"local":"/mediawiki-1.38.4/load.php"});mw.loader.register([["user.options","1i9g4",[],1],["mediawiki.skinning.elements","jvezf"],["mediawiki.skinning.content","hs5kb"],["mediawiki.skinning.interface","1b6sc"],["jquery.makeCollapsible.styles","1qdek"],["mediawiki.skinning.content.parsoid","1w1s5"],["mediawiki.skinning.content.externallinks","3e72b"],["jquery","1vnvf"],["es6-polyfills","u287e",[],null,null,
"return Array.prototype.find\u0026\u0026Array.prototype.findIndex\u0026\u0026Array.prototype.includes\u0026\u0026typeof Promise==='function'\u0026\u0026Promise.prototype.finally;"],["fetch-polyfill","1gvrd",[10]],["web2017-polyfills","k0rck",[8],null,null,"return'IntersectionObserver'in window\u0026\u0026typeof fetch==='function'\u0026\u0026typeof URL==='function'\u0026\u0026'toJSON'in URL.prototype;"],["mediawiki.base","fixjs",[7]],["jquery.client","1tje2"],["jquery.confirmable","1o3o6",[85]],["jquery.cookie","1u41n"],["jquery.highlightText","t130m",[65]],["jquery.i18n","31t4a",[84]],["jquery.lengthLimit","qrnp1",[55]],["jquery.makeCollapsible","1u92a",[4]],["jquery.spinner","yoa8f",[20]],["jquery.spinner.styles","pfek7"],["jquery.suggestions","1ykxl",[15]],["jquery.tablesorter","1rb22",[23,86,65]],["jquery.tablesorter.styles","jjsfw"],["jquery.textSelection","em3yw",[12]],["jquery.throttle-debounce","1bymo"],["jquery.ui","1mhub"],["moment","1lqla",[82,65]],["vue","3awne!"],[
"@vue/composition-api","1s4l3",[28]],["vuex","ironm!",[28]],["wvui","46zus",[29]],["wvui-search","1rr2l",[28]],["mediawiki.template","6nkqm"],["mediawiki.template.mustache","gy30q",[33]],["mediawiki.apipretty","qjpf2"],["mediawiki.api","1tynz",[60,85]],["mediawiki.content.json","1xmtc"],["mediawiki.confirmCloseWindow","12ocq"],["mediawiki.diff.styles","jiim5"],["mediawiki.feedback","1scvv",[340,162]],["mediawiki.feedlink","5bck4"],["mediawiki.ForeignApi","17f2l",[43]],["mediawiki.ForeignApi.core","15s0r",[63,36,150]],["mediawiki.helplink","5fs9z"],["mediawiki.hlist","187fe"],["mediawiki.htmlform","a9nj4",[17,65]],["mediawiki.htmlform.ooui","moc8u",[154]],["mediawiki.htmlform.styles","1x8zm"],["mediawiki.htmlform.ooui.styles","ge3zz"],["mediawiki.icon","1wbte"],["mediawiki.inspect","1w7zb",[55,65]],["mediawiki.notification","1epvo",[65,72]],["mediawiki.notification.convertmessagebox","zb0xo",[52]],["mediawiki.notification.convertmessagebox.styles","dro1f"],["mediawiki.String","1ck84"],[
"mediawiki.pager.styles","2txmq"],["mediawiki.pulsatingdot","svyap"],["mediawiki.searchSuggest","1umuo",[21,36]],["mediawiki.storage","1sj4u"],["mediawiki.Title","1bqh8",[55,65]],["mediawiki.toc","5oex3",[69]],["mediawiki.toc.styles","1rmxj"],["mediawiki.Uri","1n2iu",[65]],["mediawiki.user","1ab6a",[36,69]],["mediawiki.util","i4dvr",[12]],["mediawiki.viewport","j19gc"],["mediawiki.checkboxtoggle","nzeg7"],["mediawiki.checkboxtoggle.styles","1esmp"],["mediawiki.cookie","h5kbk",[14]],["mediawiki.experiments","8e8ao"],["mediawiki.editfont.styles","bc31w"],["mediawiki.visibleTimeout","1bmk6"],["mediawiki.action.edit","165e7",[24,74,36,71,130]],["mediawiki.action.edit.styles","1h80q"],["mediawiki.action.history","1j8pz",[18]],["mediawiki.action.history.styles","eenrw"],["mediawiki.action.view.categoryPage.styles","18sxm"],["mediawiki.action.view.redirect","1a3n8",[12]],["mediawiki.action.view.redirectPage","11n5s"],["mediawiki.action.edit.editWarning","192id",[24,38,85]],[
"mediawiki.action.styles","xz1f2"],["mediawiki.language","cll5a",[83]],["mediawiki.cldr","1630p",[84]],["mediawiki.libs.pluralruleparser","8vy0u"],["mediawiki.jqueryMsg","egibz",[55,82,65,0]],["mediawiki.language.months","h47vx",[82]],["mediawiki.language.names","hett5",[82]],["mediawiki.language.specialCharacters","b1swo",[82]],["mediawiki.libs.jpegmeta","16fc5"],["mediawiki.page.gallery.styles","19rc4"],["mediawiki.page.ready","bhr2a",[36]],["mediawiki.page.watch.ajax","cxvwj",[36]],["mediawiki.page.preview","3ldjn",[18,24,36,39,154]],["mediawiki.rcfilters.filters.base.styles","vhvy0"],["mediawiki.rcfilters.filters.ui","2zxs2",[18,63,64,125,163,170,172,173,174,176,177]],["mediawiki.interface.helpers.styles","1j78j"],["mediawiki.special","u91kd"],["mediawiki.special.apisandbox","oc6c7",[18,63,145,131,153,168]],["mediawiki.special.block","3z6jo",[46,128,144,135,145,142,168,170]],["mediawiki.misc-authed-ooui","4897z",[47,125,130]],["mediawiki.misc-authed-curate","pgwp6",[13,19,36]],[
"mediawiki.special.changeslist","nowcd"],["mediawiki.special.changeslist.watchlistexpiry","1r27q",[97]],["mediawiki.special.changeslist.legend","1yu7j"],["mediawiki.special.changeslist.legend.js","fa4m4",[18,69]],["mediawiki.special.contributions","ua2dg",[18,85,128,153]],["mediawiki.special.import.styles.ooui","1owcj"],["mediawiki.special.preferences.ooui","o99pm",[38,71,53,59,135,130]],["mediawiki.special.preferences.styles.ooui","399x3"],["mediawiki.special.recentchanges","1b2m9",[125]],["mediawiki.special.revisionDelete","e8jxp",[17]],["mediawiki.special.search","1sevh",[146]],["mediawiki.special.search.commonsInterwikiWidget","6mmvx",[63,36]],["mediawiki.special.search.interwikiwidget.styles","17wtq"],["mediawiki.special.search.styles","3xdaq"],["mediawiki.special.userlogin.common.styles","1bm7h"],["mediawiki.legacy.shared","aejeb"],["mediawiki.ui","1rfkw"],["mediawiki.ui.checkbox","15yy1"],["mediawiki.ui.radio","1pfrq"],["mediawiki.ui.anchor","c9pu1"],["mediawiki.ui.button",
"10y2f"],["mediawiki.ui.input","18jsb"],["mediawiki.ui.icon","1pq9s"],["mediawiki.widgets","1egrt",[36,126,157,167]],["mediawiki.widgets.styles","1kqtv"],["mediawiki.widgets.AbandonEditDialog","30555",[162]],["mediawiki.widgets.DateInputWidget","y0u7m",[129,27,157,178]],["mediawiki.widgets.DateInputWidget.styles","sdb9r"],["mediawiki.widgets.visibleLengthLimit","uj2nl",[17,154]],["mediawiki.widgets.datetime","19eqw",[65,154,173,177,178]],["mediawiki.widgets.expiry","1xp7z",[131,27,157]],["mediawiki.widgets.CheckMatrixWidget","bbszi",[154]],["mediawiki.widgets.CategoryMultiselectWidget","au0ux",[42,157]],["mediawiki.widgets.SelectWithInputWidget","yjlkr",[136,157]],["mediawiki.widgets.SelectWithInputWidget.styles","4wtw6"],["mediawiki.widgets.SizeFilterWidget","y5drd",[138,157]],["mediawiki.widgets.SizeFilterWidget.styles","b3yqn"],["mediawiki.widgets.MediaSearch","1065b",[42,157]],["mediawiki.widgets.Table","7slf3",[157]],["mediawiki.widgets.TagMultiselectWidget","1mwuq",[157]],[
"mediawiki.widgets.UserInputWidget","1555z",[36,157]],["mediawiki.widgets.UsersMultiselectWidget","1h6xp",[36,157]],["mediawiki.widgets.NamespacesMultiselectWidget","jiviu",[157]],["mediawiki.widgets.TitlesMultiselectWidget","593ki",[125]],["mediawiki.widgets.SearchInputWidget","haq07",[58,125,173]],["mediawiki.widgets.SearchInputWidget.styles","176ja"],["mediawiki.watchstar.widgets","1i5z8",[153]],["mediawiki.deflate","glf6m"],["oojs","1ch6v"],["mediawiki.router","ajk4o",[152]],["oojs-router","3j2x4",[150]],["oojs-ui","1gvrd",[160,157,162]],["oojs-ui-core","qxl4j",[82,150,156,155,164]],["oojs-ui-core.styles","m32um"],["oojs-ui-core.icons","1vhwb"],["oojs-ui-widgets","95x0c",[154,159]],["oojs-ui-widgets.styles","15b57"],["oojs-ui-widgets.icons","8xgbd"],["oojs-ui-toolbars","swo97",[154,161]],["oojs-ui-toolbars.icons","gktm3"],["oojs-ui-windows","hui52",[154,163]],["oojs-ui-windows.icons","eyi1j"],["oojs-ui.styles.indicators","wdgwa"],["oojs-ui.styles.icons-accessibility","bjtlh"],[
"oojs-ui.styles.icons-alerts","mqxq1"],["oojs-ui.styles.icons-content","f6dwy"],["oojs-ui.styles.icons-editing-advanced","x08b1"],["oojs-ui.styles.icons-editing-citation","1l7c7"],["oojs-ui.styles.icons-editing-core","1s0ty"],["oojs-ui.styles.icons-editing-list","ttt99"],["oojs-ui.styles.icons-editing-styling","1w978"],["oojs-ui.styles.icons-interactions","1mpw0"],["oojs-ui.styles.icons-layout","1btth"],["oojs-ui.styles.icons-location","1j3vb"],["oojs-ui.styles.icons-media","954uo"],["oojs-ui.styles.icons-moderation","1d3q3"],["oojs-ui.styles.icons-movement","1as64"],["oojs-ui.styles.icons-user","6k6aj"],["oojs-ui.styles.icons-wikimedia","3hlik"],["skins.monobook.styles","s3i0z"],["skins.monobook.scripts","1xmjx",[64,166]],["skins.timeless","af23g"],["skins.timeless.js","poasm"],["skins.vector.styles.legacy","1st1p"],["skins.vector.styles","nohlx"],["skins.vector.icons.js","1coau"],["skins.vector.icons","1ya28"],["skins.minerva.base.styles","1r7aw"],[
"skins.minerva.content.styles.images","1rp9m"],["skins.minerva.icons.loggedin","tiemu"],["skins.minerva.amc.styles","m8muw"],["skins.minerva.overflow.icons","1g93s"],["skins.minerva.icons.wikimedia","1tg7q"],["skins.minerva.icons.images.scripts.misc","hwwkt"],["skins.minerva.icons.page.issues.uncolored","mx8bu"],["skins.minerva.icons.page.issues.default.color","md3ff"],["skins.minerva.icons.page.issues.medium.color","gn22b"],["skins.minerva.mainPage.styles","1k040"],["skins.minerva.userpage.styles","ml320"],["skins.minerva.talk.styles","1u1pe"],["skins.minerva.personalMenu.icons","1fqbu"],["skins.minerva.mainMenu.advanced.icons","1qk47"],["skins.minerva.mainMenu.icons","pb3px"],["skins.minerva.mainMenu.styles","1lt7r"],["skins.minerva.loggedin.styles","1gdzj"],["skins.minerva.scripts","xh48m",[63,70,121,219,195,197,198,196,204,205,208]],["skins.minerva.messageBox.styles","1xpez"],["skins.minerva.categories.styles","1u8sc"],["mobile.pagelist.styles","fig1g"],["mobile.pagesummary.styles"
,"vgkev"],["mobile.placeholder.images","vzzo2"],["mobile.userpage.styles","1o6ra"],["mobile.startup.images","1b1fw"],["mobile.init.styles","1hu3b"],["mobile.init","113eu",[63,219]],["mobile.ooui.icons","igx4n"],["mobile.user.icons","1ixlw"],["mobile.startup","1fjna",[25,92,151,59,34,122,124,64,217,210,211,212,214]],["mobile.editor.overlay","zq0ck",[38,71,52,123,127,221,219,218,153,170]],["mobile.editor.images","jcrul"],["mobile.talk.overlays","caod9",[121,220]],["mobile.mediaViewer","kgy2d",[219]],["mobile.languages.structured","tk49g",[219]],["mobile.site","1gvrd",["site"]],["mobile.site.styles","1gvrd",["site.styles"]],["mobile.special.styles","5bzj9"],["mobile.special.watchlist.scripts","fqfxf",[219]],["mobile.special.mobileoptions.styles","8494e"],["mobile.special.mobileoptions.scripts","1iz3s",[219]],["mobile.special.nearby.styles","3xrw4"],["mobile.special.userlogin.scripts","1rm8n"],["mobile.special.nearby.scripts","aaqar",[63,231,219]],["mobile.special.history.styles","1ldsf"],
["mobile.special.pagefeed.styles","2ecap"],["mobile.special.mobilediff.images","yk8c1"],["mobile.special.mobilediff.styles","1vr69"],["ext.cite.styles","17mnd"],["ext.cite.style","yx4l1"],["ext.cite.visualEditor.core","1hci2",[317]],["ext.cite.visualEditor","va9o0",[239,238,240,166,169,173]],["ext.extjsbase.all","ndrnu"],["ext.extjsbase.charts","x0gxn",[242]],["ext.extjsbase.ux","1bqxw",[242]],["ext.extjsbase.MWExt","zthmj",[242]],["ext.eventLogging","wqxqh",[64]],["ext.eventLogging.debug","aryjb"],["ext.centralNotice.startUp","1ptwd",[250,65]],["ext.centralNotice.geoIP","spv2q",[14]],["ext.centralNotice.choiceData","1hm0r"],["ext.centralNotice.display","1kr7o",[249,252,246,63,59]],["ext.centralNotice.kvStore","yqh2i"],["ext.centralNotice.bannerHistoryLogger","qedmi",[251]],["ext.centralNotice.impressionDiet","n5zrg",[251]],["ext.centralNotice.largeBannerLimit","p2grr",[251]],["ext.centralNotice.legacySupport","1kh3o",[251]],["ext.centralNotice.bannerSequence","q58r6",[251]],[
"ext.centralNotice.freegeoipLookup","1ab6b",[249]],["ext.centralNotice.impressionEventsSampleRate","1kg37",[251]],["ext.echo.logger","zy2sg",[64,150]],["ext.echo.ui","1svib",[262,260,338,157,166,167,173,177,178,179]],["ext.echo.dm","1tz62",[265,27]],["ext.echo.api","wqab8",[42]],["ext.echo.mobile","1txo2",[261,151,34]],["ext.echo.init","9h5zq",[263]],["ext.echo.styles.badge","1l40v"],["ext.echo.styles.notifications","16fds"],["ext.echo.styles.alert","ch7jy"],["ext.echo.special","a60q5",[270,261]],["ext.echo.styles.special","1djtg"],["ext.thanks.images","mzput"],["ext.thanks.mobilediff","1ksxt",[271,219]],["ext.network","srvty",[335]],["ext.network.special","182ji",[60,157]],["ext.wikimediaBadges","1stt8"],["ext.betaFeatures","l8mgc",[12,154]],["ext.betaFeatures.styles","8rn4k"],["socket.io","fcmug"],["dompurify","cw5fe"],["color-picker","1hxf4"],["unicodejs","alrva"],["papaparse","5tm70"],["rangefix","ekvqx"],["spark-md5","1uk2w"],["ext.visualEditor.supportCheck","hzrm9",[],2],[
"ext.visualEditor.sanitize","1snr7",[279,299],2],["ext.visualEditor.progressBarWidget","1ns9r",[],2],["ext.visualEditor.tempWikitextEditorWidget","151cl",[71,64],2],["ext.visualEditor.targetLoader","20936",[298,296,24,63,59,64],2],["ext.visualEditor.mobileArticleTarget","1khnv",[302,307],2],["ext.visualEditor.collabTarget","1mho3",[300,306,71,125,173,174],2],["ext.visualEditor.collabTarget.mobile","1b0rg",[291,307,311],2],["ext.visualEditor.collabTarget.init","1sgot",[285,125,153],2],["ext.visualEditor.collabTarget.init.styles","18e9s"],["ext.visualEditor.ve","1mbx1",[],2],["ext.visualEditor.track","1lfjv",[295],2],["ext.visualEditor.core.utils","fkgtc",[296,153],2],["ext.visualEditor.core.utils.parsing","1584k",[295],2],["ext.visualEditor.base","51brd",[297,298,281],2],["ext.visualEditor.mediawiki","pacuv",[299,289,22,339],2],["ext.visualEditor.mwsave","14sxu",[310,17,19,39,173],2],["ext.visualEditor.articleTarget","1tuzr",[311,301,127],2],["ext.visualEditor.data","1ywhl",[300]],[
"ext.visualEditor.core","13r36",[286,285,12,282,283,284],2],["ext.visualEditor.commentAnnotation","vqcsh",[304],2],["ext.visualEditor.rebase","ls5cr",[280,320,305,179,278],2],["ext.visualEditor.core.mobile","11l7i",[304],2],["ext.visualEditor.welcome","14vz4",[153],2],["ext.visualEditor.switching","o9sd3",[36,153,165,168,170],2],["ext.visualEditor.mwcore","1o64l",[321,300,309,308,96,57,5,125],2],["ext.visualEditor.mwextensions","1gvrd",[303,332,325,327,312,329,314,326,315,317],2],["ext.visualEditor.mwformatting","rz1n5",[310],2],["ext.visualEditor.mwimage.core","1xo1s",[310],2],["ext.visualEditor.mwimage","16xmb",[313,139,27,176,180],2],["ext.visualEditor.mwlink","256rz",[310],2],["ext.visualEditor.mwmeta","lgfn3",[315,79],2],["ext.visualEditor.mwtransclusion","1y9um",[310,142],2],["treeDiffer","ylkzm"],["diffMatchPatch","1f0tq"],["ext.visualEditor.checkList","zna1b",[304],2],["ext.visualEditor.diffing","vu52x",[319,304,318],2],["ext.visualEditor.diffPage.init.styles","9zuqr"],[
"ext.visualEditor.diffLoader","1un0a",[289],2],["ext.visualEditor.diffPage.init","r5rvs",[323,153,165,168],2],["ext.visualEditor.language","189v3",[304,339,87],2],["ext.visualEditor.mwlanguage","16d5w",[304],2],["ext.visualEditor.mwalienextension","1i211",[310],2],["ext.visualEditor.mwwikitext","yxnxl",[315,71],2],["ext.visualEditor.mwgallery","gjsw9",[310,90,139,176],2],["ext.visualEditor.mwsignature","18h8y",[317],2],["ext.visualEditor.experimental","1gvrd",[],2],["ext.visualEditor.icons","1gvrd",[333,334,166,167,168,170,171,172,173,174,177,178,179,164],2],["ext.visualEditor.moduleIcons","13j4y"],["ext.visualEditor.moduleIndicators","z57jk"],["mediawiki.api.edit","1gvrd",[36]],["mobile.editor.ve","rfpnf",[290,220]],["ext.echo.emailicons","129nw"],["ext.echo.secondaryicons","vahgf"],["jquery.uls.data","qxpko"],["mediawiki.messagePoster","gdbf7",[42]]]);mw.config.set(window.RLCONF||{});mw.loader.state(window.RLSTATE||{});mw.loader.load(window.RLPAGEMODULES||[]);queue=window.RLQ||[];RLQ
=[];RLQ.push=function(fn){if(typeof fn==='function'){fn();}else{RLQ[RLQ.length]=fn;}};while(queue[0]){RLQ.push(queue.shift());}NORLQ={push:function(){}};}());}
