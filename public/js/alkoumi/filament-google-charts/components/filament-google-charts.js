var dr=Object.defineProperty;var s=(r,e)=>()=>(r&&(e=r(r=0)),e);var xe=(r,e)=>{for(var t in e)dr(r,t,{get:e[t],enumerable:!0})};var se,ae,Te,Ge,j,Ke,ve,qe,_e,$e=s(()=>{se=globalThis,ae=se.ShadowRoot&&(se.ShadyCSS===void 0||se.ShadyCSS.nativeShadow)&&"adoptedStyleSheets"in Document.prototype&&"replace"in CSSStyleSheet.prototype,Te=Symbol(),Ge=new WeakMap,j=class{constructor(e,t,i){if(this._$cssResult$=!0,i!==Te)throw Error("CSSResult is not constructable. Use `unsafeCSS` or `css` instead.");this.cssText=e,this.t=t}get styleSheet(){let e=this.o,t=this.t;if(ae&&e===void 0){let i=t!==void 0&&t.length===1;i&&(e=Ge.get(t)),e===void 0&&((this.o=e=new CSSStyleSheet).replaceSync(this.cssText),i&&Ge.set(t,e))}return e}toString(){return this.cssText}},Ke=r=>new j(typeof r=="string"?r:r+"",void 0,Te),ve=(r,...e)=>{let t=r.length===1?r[0]:e.reduce((i,n,o)=>i+(a=>{if(a._$cssResult$===!0)return a.cssText;if(typeof a=="number")return a;throw Error("Value passed to 'css' function must be a 'css' function result: "+a+". Use 'unsafeCSS' to pass non-literal values, but take care to ensure page security.")})(n)+r[o+1],r[0]);return new j(t,r,Te)},qe=(r,e)=>{if(ae)r.adoptedStyleSheets=e.map(t=>t instanceof CSSStyleSheet?t:t.styleSheet);else for(let t of e){let i=document.createElement("style"),n=se.litNonce;n!==void 0&&i.setAttribute("nonce",n),i.textContent=t.cssText,r.appendChild(i)}},_e=ae?r=>r:r=>r instanceof CSSStyleSheet?(e=>{let t="";for(let i of e.cssRules)t+=i.cssText;return Ke(t)})(r):r});var hr,fr,mr,gr,Er,wr,_,Ze,Sr,yr,W,B,le,Ye,x,G=s(()=>{$e();$e();({is:hr,defineProperty:fr,getOwnPropertyDescriptor:mr,getOwnPropertyNames:gr,getOwnPropertySymbols:Er,getPrototypeOf:wr}=Object),_=globalThis,Ze=_.trustedTypes,Sr=Ze?Ze.emptyScript:"",yr=_.reactiveElementPolyfillSupport,W=(r,e)=>r,B={toAttribute(r,e){switch(e){case Boolean:r=r?Sr:null;break;case Object:case Array:r=r==null?r:JSON.stringify(r)}return r},fromAttribute(r,e){let t=r;switch(e){case Boolean:t=r!==null;break;case Number:t=r===null?null:Number(r);break;case Object:case Array:try{t=JSON.parse(r)}catch{t=null}}return t}},le=(r,e)=>!hr(r,e),Ye={attribute:!0,type:String,converter:B,reflect:!1,useDefault:!1,hasChanged:le};Symbol.metadata??(Symbol.metadata=Symbol("metadata")),_.litPropertyMetadata??(_.litPropertyMetadata=new WeakMap);x=class extends HTMLElement{static addInitializer(e){this._$Ei(),(this.l??(this.l=[])).push(e)}static get observedAttributes(){return this.finalize(),this._$Eh&&[...this._$Eh.keys()]}static createProperty(e,t=Ye){if(t.state&&(t.attribute=!1),this._$Ei(),this.prototype.hasOwnProperty(e)&&((t=Object.create(t)).wrapped=!0),this.elementProperties.set(e,t),!t.noAccessor){let i=Symbol(),n=this.getPropertyDescriptor(e,i,t);n!==void 0&&fr(this.prototype,e,n)}}static getPropertyDescriptor(e,t,i){let{get:n,set:o}=mr(this.prototype,e)??{get(){return this[t]},set(a){this[t]=a}};return{get:n,set(a){let c=n?.call(this);o?.call(this,a),this.requestUpdate(e,c,i)},configurable:!0,enumerable:!0}}static getPropertyOptions(e){return this.elementProperties.get(e)??Ye}static _$Ei(){if(this.hasOwnProperty(W("elementProperties")))return;let e=wr(this);e.finalize(),e.l!==void 0&&(this.l=[...e.l]),this.elementProperties=new Map(e.elementProperties)}static finalize(){if(this.hasOwnProperty(W("finalized")))return;if(this.finalized=!0,this._$Ei(),this.hasOwnProperty(W("properties"))){let t=this.properties,i=[...gr(t),...Er(t)];for(let n of i)this.createProperty(n,t[n])}let e=this[Symbol.metadata];if(e!==null){let t=litPropertyMetadata.get(e);if(t!==void 0)for(let[i,n]of t)this.elementProperties.set(i,n)}this._$Eh=new Map;for(let[t,i]of this.elementProperties){let n=this._$Eu(t,i);n!==void 0&&this._$Eh.set(n,t)}this.elementStyles=this.finalizeStyles(this.styles)}static finalizeStyles(e){let t=[];if(Array.isArray(e)){let i=new Set(e.flat(1/0).reverse());for(let n of i)t.unshift(_e(n))}else e!==void 0&&t.push(_e(e));return t}static _$Eu(e,t){let i=t.attribute;return i===!1?void 0:typeof i=="string"?i:typeof e=="string"?e.toLowerCase():void 0}constructor(){super(),this._$Ep=void 0,this.isUpdatePending=!1,this.hasUpdated=!1,this._$Em=null,this._$Ev()}_$Ev(){this._$ES=new Promise(e=>this.enableUpdating=e),this._$AL=new Map,this._$E_(),this.requestUpdate(),this.constructor.l?.forEach(e=>e(this))}addController(e){(this._$EO??(this._$EO=new Set)).add(e),this.renderRoot!==void 0&&this.isConnected&&e.hostConnected?.()}removeController(e){this._$EO?.delete(e)}_$E_(){let e=new Map,t=this.constructor.elementProperties;for(let i of t.keys())this.hasOwnProperty(i)&&(e.set(i,this[i]),delete this[i]);e.size>0&&(this._$Ep=e)}createRenderRoot(){let e=this.shadowRoot??this.attachShadow(this.constructor.shadowRootOptions);return qe(e,this.constructor.elementStyles),e}connectedCallback(){this.renderRoot??(this.renderRoot=this.createRenderRoot()),this.enableUpdating(!0),this._$EO?.forEach(e=>e.hostConnected?.())}enableUpdating(e){}disconnectedCallback(){this._$EO?.forEach(e=>e.hostDisconnected?.())}attributeChangedCallback(e,t,i){this._$AK(e,i)}_$ET(e,t){let i=this.constructor.elementProperties.get(e),n=this.constructor._$Eu(e,i);if(n!==void 0&&i.reflect===!0){let o=(i.converter?.toAttribute!==void 0?i.converter:B).toAttribute(t,i.type);this._$Em=e,o==null?this.removeAttribute(n):this.setAttribute(n,o),this._$Em=null}}_$AK(e,t){let i=this.constructor,n=i._$Eh.get(e);if(n!==void 0&&this._$Em!==n){let o=i.getPropertyOptions(n),a=typeof o.converter=="function"?{fromAttribute:o.converter}:o.converter?.fromAttribute!==void 0?o.converter:B;this._$Em=n;let c=a.fromAttribute(t,o.type);this[n]=c??this._$Ej?.get(n)??c,this._$Em=null}}requestUpdate(e,t,i,n=!1,o){if(e!==void 0){let a=this.constructor;if(n===!1&&(o=this[e]),i??(i=a.getPropertyOptions(e)),!((i.hasChanged??le)(o,t)||i.useDefault&&i.reflect&&o===this._$Ej?.get(e)&&!this.hasAttribute(a._$Eu(e,i))))return;this.C(e,t,i)}this.isUpdatePending===!1&&(this._$ES=this._$EP())}C(e,t,{useDefault:i,reflect:n,wrapped:o},a){i&&!(this._$Ej??(this._$Ej=new Map)).has(e)&&(this._$Ej.set(e,a??t??this[e]),o!==!0||a!==void 0)||(this._$AL.has(e)||(this.hasUpdated||i||(t=void 0),this._$AL.set(e,t)),n===!0&&this._$Em!==e&&(this._$Eq??(this._$Eq=new Set)).add(e))}async _$EP(){this.isUpdatePending=!0;try{await this._$ES}catch(t){Promise.reject(t)}let e=this.scheduleUpdate();return e!=null&&await e,!this.isUpdatePending}scheduleUpdate(){return this.performUpdate()}performUpdate(){if(!this.isUpdatePending)return;if(!this.hasUpdated){if(this.renderRoot??(this.renderRoot=this.createRenderRoot()),this._$Ep){for(let[n,o]of this._$Ep)this[n]=o;this._$Ep=void 0}let i=this.constructor.elementProperties;if(i.size>0)for(let[n,o]of i){let{wrapped:a}=o,c=this[n];a!==!0||this._$AL.has(n)||c===void 0||this.C(n,void 0,o,c)}}let e=!1,t=this._$AL;try{e=this.shouldUpdate(t),e?(this.willUpdate(t),this._$EO?.forEach(i=>i.hostUpdate?.()),this.update(t)):this._$EM()}catch(i){throw e=!1,this._$EM(),i}e&&this._$AE(t)}willUpdate(e){}_$AE(e){this._$EO?.forEach(t=>t.hostUpdated?.()),this.hasUpdated||(this.hasUpdated=!0,this.firstUpdated(e)),this.updated(e)}_$EM(){this._$AL=new Map,this.isUpdatePending=!1}get updateComplete(){return this.getUpdateComplete()}getUpdateComplete(){return this._$ES}shouldUpdate(e){return!0}update(e){this._$Eq&&(this._$Eq=this._$Eq.forEach(t=>this._$ET(t,this[t]))),this._$EM()}updated(e){}firstUpdated(e){}};x.elementStyles=[],x.shadowRootOptions={mode:"open"},x[W("elementProperties")]=new Map,x[W("finalized")]=new Map,yr?.({ReactiveElement:x}),(_.reactiveElementVersions??(_.reactiveElementVersions=[])).push("2.1.2")});function lt(r,e){if(!Ie(r)||!r.hasOwnProperty("raw"))throw Error("invalid template strings array");return Xe!==void 0?Xe.createHTML(e):e}function M(r,e,t=r,i){if(e===z)return e;let n=i!==void 0?t._$Co?.[i]:t._$Cl,o=Y(e)?void 0:e._$litDirective$;return n?.constructor!==o&&(n?._$AO?.(!1),o===void 0?n=void 0:(n=new o(r),n._$AT(r,t,i)),i!==void 0?(t._$Co??(t._$Co=[]))[i]=n:t._$Cl=n),n!==void 0&&(e=M(r,n._$AS(r,e.values),n,i)),e}var q,Je,ce,Xe,nt,$,ot,Ar,I,Z,Y,Ie,br,Ne,K,Qe,et,U,tt,rt,st,ze,at,Si,yi,z,h,it,R,xr,J,Oe,X,H,Ce,Pe,Ue,Re,Tr,ct,pe=s(()=>{q=globalThis,Je=r=>r,ce=q.trustedTypes,Xe=ce?ce.createPolicy("lit-html",{createHTML:r=>r}):void 0,nt="$lit$",$=`lit$${Math.random().toFixed(9).slice(2)}$`,ot="?"+$,Ar=`<${ot}>`,I=document,Z=()=>I.createComment(""),Y=r=>r===null||typeof r!="object"&&typeof r!="function",Ie=Array.isArray,br=r=>Ie(r)||typeof r?.[Symbol.iterator]=="function",Ne=`[ 	
\f\r]`,K=/<(?:(!--|\/[^a-zA-Z])|(\/?[a-zA-Z][^>\s]*)|(\/?$))/g,Qe=/-->/g,et=/>/g,U=RegExp(`>|${Ne}(?:([^\\s"'>=/]+)(${Ne}*=${Ne}*(?:[^ 	
\f\r"'\`<>=]|("|')|))|$)`,"g"),tt=/'/g,rt=/"/g,st=/^(?:script|style|textarea|title)$/i,ze=r=>(e,...t)=>({_$litType$:r,strings:e,values:t}),at=ze(1),Si=ze(2),yi=ze(3),z=Symbol.for("lit-noChange"),h=Symbol.for("lit-nothing"),it=new WeakMap,R=I.createTreeWalker(I,129);xr=(r,e)=>{let t=r.length-1,i=[],n,o=e===2?"<svg>":e===3?"<math>":"",a=K;for(let c=0;c<t;c++){let l=r[c],d,m,p=-1,b=0;for(;b<l.length&&(a.lastIndex=b,m=a.exec(l),m!==null);)b=a.lastIndex,a===K?m[1]==="!--"?a=Qe:m[1]!==void 0?a=et:m[2]!==void 0?(st.test(m[2])&&(n=RegExp("</"+m[2],"g")),a=U):m[3]!==void 0&&(a=U):a===U?m[0]===">"?(a=n??K,p=-1):m[1]===void 0?p=-2:(p=a.lastIndex-m[2].length,d=m[1],a=m[3]===void 0?U:m[3]==='"'?rt:tt):a===rt||a===tt?a=U:a===Qe||a===et?a=K:(a=U,n=void 0);let v=a===U&&r[c+1].startsWith("/>")?" ":"";o+=a===K?l+Ar:p>=0?(i.push(d),l.slice(0,p)+nt+l.slice(p)+$+v):l+$+(p===-2?c:v)}return[lt(r,o+(r[t]||"<?>")+(e===2?"</svg>":e===3?"</math>":"")),i]},J=class r{constructor({strings:e,_$litType$:t},i){let n;this.parts=[];let o=0,a=0,c=e.length-1,l=this.parts,[d,m]=xr(e,t);if(this.el=r.createElement(d,i),R.currentNode=this.el.content,t===2||t===3){let p=this.el.content.firstChild;p.replaceWith(...p.childNodes)}for(;(n=R.nextNode())!==null&&l.length<c;){if(n.nodeType===1){if(n.hasAttributes())for(let p of n.getAttributeNames())if(p.endsWith(nt)){let b=m[a++],v=n.getAttribute(p).split($),oe=/([.?@])?(.*)/.exec(b);l.push({type:1,index:o,name:oe[2],strings:v,ctor:oe[1]==="."?Ce:oe[1]==="?"?Pe:oe[1]==="@"?Ue:H}),n.removeAttribute(p)}else p.startsWith($)&&(l.push({type:6,index:o}),n.removeAttribute(p));if(st.test(n.tagName)){let p=n.textContent.split($),b=p.length-1;if(b>0){n.textContent=ce?ce.emptyScript:"";for(let v=0;v<b;v++)n.append(p[v],Z()),R.nextNode(),l.push({type:2,index:++o});n.append(p[b],Z())}}}else if(n.nodeType===8)if(n.data===ot)l.push({type:2,index:o});else{let p=-1;for(;(p=n.data.indexOf($,p+1))!==-1;)l.push({type:7,index:o}),p+=$.length-1}o++}}static createElement(e,t){let i=I.createElement("template");return i.innerHTML=e,i}};Oe=class{constructor(e,t){this._$AV=[],this._$AN=void 0,this._$AD=e,this._$AM=t}get parentNode(){return this._$AM.parentNode}get _$AU(){return this._$AM._$AU}u(e){let{el:{content:t},parts:i}=this._$AD,n=(e?.creationScope??I).importNode(t,!0);R.currentNode=n;let o=R.nextNode(),a=0,c=0,l=i[0];for(;l!==void 0;){if(a===l.index){let d;l.type===2?d=new X(o,o.nextSibling,this,e):l.type===1?d=new l.ctor(o,l.name,l.strings,this,e):l.type===6&&(d=new Re(o,this,e)),this._$AV.push(d),l=i[++c]}a!==l?.index&&(o=R.nextNode(),a++)}return R.currentNode=I,n}p(e){let t=0;for(let i of this._$AV)i!==void 0&&(i.strings!==void 0?(i._$AI(e,i,t),t+=i.strings.length-2):i._$AI(e[t])),t++}},X=class r{get _$AU(){return this._$AM?._$AU??this._$Cv}constructor(e,t,i,n){this.type=2,this._$AH=h,this._$AN=void 0,this._$AA=e,this._$AB=t,this._$AM=i,this.options=n,this._$Cv=n?.isConnected??!0}get parentNode(){let e=this._$AA.parentNode,t=this._$AM;return t!==void 0&&e?.nodeType===11&&(e=t.parentNode),e}get startNode(){return this._$AA}get endNode(){return this._$AB}_$AI(e,t=this){e=M(this,e,t),Y(e)?e===h||e==null||e===""?(this._$AH!==h&&this._$AR(),this._$AH=h):e!==this._$AH&&e!==z&&this._(e):e._$litType$!==void 0?this.$(e):e.nodeType!==void 0?this.T(e):br(e)?this.k(e):this._(e)}O(e){return this._$AA.parentNode.insertBefore(e,this._$AB)}T(e){this._$AH!==e&&(this._$AR(),this._$AH=this.O(e))}_(e){this._$AH!==h&&Y(this._$AH)?this._$AA.nextSibling.data=e:this.T(I.createTextNode(e)),this._$AH=e}$(e){let{values:t,_$litType$:i}=e,n=typeof i=="number"?this._$AC(e):(i.el===void 0&&(i.el=J.createElement(lt(i.h,i.h[0]),this.options)),i);if(this._$AH?._$AD===n)this._$AH.p(t);else{let o=new Oe(n,this),a=o.u(this.options);o.p(t),this.T(a),this._$AH=o}}_$AC(e){let t=it.get(e.strings);return t===void 0&&it.set(e.strings,t=new J(e)),t}k(e){Ie(this._$AH)||(this._$AH=[],this._$AR());let t=this._$AH,i,n=0;for(let o of e)n===t.length?t.push(i=new r(this.O(Z()),this.O(Z()),this,this.options)):i=t[n],i._$AI(o),n++;n<t.length&&(this._$AR(i&&i._$AB.nextSibling,n),t.length=n)}_$AR(e=this._$AA.nextSibling,t){for(this._$AP?.(!1,!0,t);e!==this._$AB;){let i=Je(e).nextSibling;Je(e).remove(),e=i}}setConnected(e){this._$AM===void 0&&(this._$Cv=e,this._$AP?.(e))}},H=class{get tagName(){return this.element.tagName}get _$AU(){return this._$AM._$AU}constructor(e,t,i,n,o){this.type=1,this._$AH=h,this._$AN=void 0,this.element=e,this.name=t,this._$AM=n,this.options=o,i.length>2||i[0]!==""||i[1]!==""?(this._$AH=Array(i.length-1).fill(new String),this.strings=i):this._$AH=h}_$AI(e,t=this,i,n){let o=this.strings,a=!1;if(o===void 0)e=M(this,e,t,0),a=!Y(e)||e!==this._$AH&&e!==z,a&&(this._$AH=e);else{let c=e,l,d;for(e=o[0],l=0;l<o.length-1;l++)d=M(this,c[i+l],t,l),d===z&&(d=this._$AH[l]),a||(a=!Y(d)||d!==this._$AH[l]),d===h?e=h:e!==h&&(e+=(d??"")+o[l+1]),this._$AH[l]=d}a&&!n&&this.j(e)}j(e){e===h?this.element.removeAttribute(this.name):this.element.setAttribute(this.name,e??"")}},Ce=class extends H{constructor(){super(...arguments),this.type=3}j(e){this.element[this.name]=e===h?void 0:e}},Pe=class extends H{constructor(){super(...arguments),this.type=4}j(e){this.element.toggleAttribute(this.name,!!e&&e!==h)}},Ue=class extends H{constructor(e,t,i,n,o){super(e,t,i,n,o),this.type=5}_$AI(e,t=this){if((e=M(this,e,t,0)??h)===z)return;let i=this._$AH,n=e===h&&i!==h||e.capture!==i.capture||e.once!==i.once||e.passive!==i.passive,o=e!==h&&(i===h||n);n&&this.element.removeEventListener(this.name,this,i),o&&this.element.addEventListener(this.name,this,e),this._$AH=e}handleEvent(e){typeof this._$AH=="function"?this._$AH.call(this.options?.host??this.element,e):this._$AH.handleEvent(e)}},Re=class{constructor(e,t,i){this.element=e,this.type=6,this._$AN=void 0,this._$AM=t,this.options=i}get _$AU(){return this._$AM._$AU}_$AI(e){M(this,e)}},Tr=q.litHtmlPolyfillSupport;Tr?.(J,X),(q.litHtmlVersions??(q.litHtmlVersions=[])).push("3.3.2");ct=(r,e,t)=>{let i=t?.renderBefore??e,n=i._$litPart$;if(n===void 0){let o=t?.renderBefore??null;i._$litPart$=n=new X(e.insertBefore(Z(),o),o,void 0,t??{})}return n._$AI(r),n}});var Q,N,vr,pt=s(()=>{G();G();pe();pe();Q=globalThis,N=class extends x{constructor(){super(...arguments),this.renderOptions={host:this},this._$Do=void 0}createRenderRoot(){var t;let e=super.createRenderRoot();return(t=this.renderOptions).renderBefore??(t.renderBefore=e.firstChild),e}update(e){let t=this.render();this.hasUpdated||(this.renderOptions.isConnected=this.isConnected),super.update(e),this._$Do=ct(t,this.renderRoot,this.renderOptions)}connectedCallback(){super.connectedCallback(),this._$Do?.setConnected(!0)}disconnectedCallback(){super.disconnectedCallback(),this._$Do?.setConnected(!1)}render(){return z}};N._$litElement$=!0,N.finalized=!0,Q.litElementHydrateSupport?.({LitElement:N});vr=Q.litElementPolyfillSupport;vr?.({LitElement:N});(Q.litElementVersions??(Q.litElementVersions=[])).push("4.2.2")});var ut=s(()=>{});var dt=s(()=>{G();pe();pt();ut()});var ht=s(()=>{});function S(r){return(e,t)=>typeof t=="object"?$r(r,e,t):((i,n,o)=>{let a=n.hasOwnProperty(o);return n.constructor.createProperty(o,i),a?Object.getOwnPropertyDescriptor(n,o):void 0})(r,e,t)}var _r,$r,Le=s(()=>{G();_r={attribute:!0,type:String,converter:B,reflect:!1,hasChanged:le},$r=(r=_r,e,t)=>{let{kind:i,metadata:n}=t,o=globalThis.litPropertyMetadata.get(n);if(o===void 0&&globalThis.litPropertyMetadata.set(n,o=new Map),i==="setter"&&((r=Object.create(r)).wrapped=!0),o.set(t.name,r),i==="accessor"){let{name:a}=t;return{set(c){let l=e.get.call(this);e.set.call(this,c),this.requestUpdate(a,l,r,!0,c)},init(c){return c!==void 0&&this.C(a,void 0,r,c),c}}}if(i==="setter"){let{name:a}=t;return function(c){let l=this[a];e.call(this,c),this.requestUpdate(a,l,r,!0,c)}}throw Error("Unsupported decorator location: "+i)}});var ft=s(()=>{Le();});var mt=s(()=>{});var k=s(()=>{});var gt=s(()=>{k();});var Et=s(()=>{k();});var wt=s(()=>{k();});var St=s(()=>{k();});var yt=s(()=>{k();});var At=s(()=>{ht();Le();ft();mt();gt();Et();wt();St();yt()});var f=s(()=>{"use strict";});var O,C=s(()=>{f();O={}});var de=s(()=>{f();C();});function he(r){return Object.isFrozen(r)&&Object.isFrozen(r.raw)}function fe(r){return r.toString().indexOf("`")===-1}var dn,hn,te=s(()=>{dn=fe(r=>r``)||fe(r=>r`\0`)||fe(r=>r`\n`)||fe(r=>r`\u0000`),hn=he``&&he`\0`&&he`\n`&&he`\u0000`});var bt=s(()=>{});var xt=s(()=>{f();de();te();bt();});function Nr(){if(typeof window<"u")return window.trustedTypes}function V(){var r;return Tt!==""&&(r=Nr())!==null&&r!==void 0?r:null}function ge(){var r,e;if(me===void 0)try{me=(e=(r=V())===null||r===void 0?void 0:r.createPolicy(Tt,{createHTML:t=>t,createScript:t=>t,createScriptURL:t=>t}))!==null&&e!==void 0?e:null}catch{me=null}return me}var Tt,me,Ee=s(()=>{Tt="google#safe"});var Nn,y=s(()=>{f();C();Ee();Nn=typeof window<"u"?window.TrustedHTML:void 0});function De(r){var e;let t=r,i=(e=ge())===null||e===void 0?void 0:e.createScriptURL(t);return i??new we(t,O)}function E(r){var e;if(!((e=V())===null||e===void 0)&&e.isScriptURL(r))return r;if(r instanceof we)return r.privateDoNotAccessOrElseWrappedResourceUrl;{let t="";throw new Error(t)}}var we,Rn,w=s(()=>{f();C();Ee();we=class{constructor(e,t){this.privateDoNotAccessOrElseWrappedResourceUrl=e}toString(){return this.privateDoNotAccessOrElseWrappedResourceUrl.toString()}},Rn=typeof window<"u"?window.TrustedScriptURL:void 0});function L(r){var e;if(!((e=V())===null||e===void 0)&&e.isScript(r))return r;if(r instanceof Me)return r.privateDoNotAccessOrElseWrappedScript;{let t="";throw new Error(t)}}var Me,Mn,P=s(()=>{f();C();Ee();Me=class{constructor(e,t){this.privateDoNotAccessOrElseWrappedScript=e}toString(){return this.privateDoNotAccessOrElseWrappedScript.toString()}},Mn=typeof window<"u"?window.TrustedScript:void 0});var vt=s(()=>{y();w();P();});var A=s(()=>{f();});var He=s(()=>{y();});var _t=s(()=>{He();y();});var $t=s(()=>{});var re,u,ye=s(()=>{re=class{constructor(e,t,i,n,o){this.allowedElements=e,this.elementPolicies=t,this.allowedGlobalAttributes=i,this.globalAttributePolicies=n,this.globallyAllowedAttributePrefixes=o}isAllowedElement(e){return e!=="FORM"&&(this.allowedElements.has(e)||this.elementPolicies.has(e))}getAttributePolicy(e,t){let i=this.elementPolicies.get(t);if(i?.has(e))return i.get(e);if(this.allowedGlobalAttributes.has(e))return{policyAction:u.KEEP};let n=this.globalAttributePolicies.get(e);return n||(this.globallyAllowedAttributePrefixes&&[...this.globallyAllowedAttributePrefixes].some(o=>e.indexOf(o)===0)?{policyAction:u.KEEP}:{policyAction:u.DROP})}};(function(r){r[r.DROP=0]="DROP",r[r.KEEP=1]="KEEP",r[r.KEEP_AND_SANITIZE_URL=2]="KEEP_AND_SANITIZE_URL",r[r.KEEP_AND_NORMALIZE=3]="KEEP_AND_NORMALIZE",r[r.KEEP_AND_SANITIZE_STYLE=4]="KEEP_AND_SANITIZE_STYLE"})(u||(u={}))});var Pr,Ur,Rr,Ir,Nt,ke=s(()=>{ye();Pr=["ARTICLE","SECTION","NAV","ASIDE","H1","H2","H3","H4","H5","H6","HEADER","FOOTER","ADDRESS","P","HR","PRE","BLOCKQUOTE","OL","UL","LH","LI","DL","DT","DD","FIGURE","FIGCAPTION","MAIN","DIV","EM","STRONG","SMALL","S","CITE","Q","DFN","ABBR","RUBY","RB","RT","RTC","RP","DATA","TIME","CODE","VAR","SAMP","KBD","SUB","SUP","I","B","U","MARK","BDI","BDO","SPAN","BR","WBR","INS","DEL","PICTURE","PARAM","TRACK","MAP","TABLE","CAPTION","COLGROUP","COL","TBODY","THEAD","TFOOT","TR","TD","TH","SELECT","DATALIST","OPTGROUP","OPTION","OUTPUT","PROGRESS","METER","FIELDSET","LEGEND","DETAILS","SUMMARY","MENU","DIALOG","SLOT","CANVAS","FONT","CENTER","ACRONYM","BASEFONT","BIG","DIR","HGROUP","STRIKE","TT"],Ur=[["A",new Map([["href",{policyAction:u.KEEP_AND_SANITIZE_URL}]])],["AREA",new Map([["href",{policyAction:u.KEEP_AND_SANITIZE_URL}]])],["LINK",new Map([["href",{policyAction:u.KEEP_AND_SANITIZE_URL,conditions:new Map([["rel",new Set(["alternate","author","bookmark","canonical","cite","help","icon","license","next","prefetch","dns-prefetch","prerender","preconnect","preload","prev","search","subresource"])]])}]])],["SOURCE",new Map([["src",{policyAction:u.KEEP}]])],["IMG",new Map([["src",{policyAction:u.KEEP}]])],["VIDEO",new Map([["src",{policyAction:u.KEEP}]])],["AUDIO",new Map([["src",{policyAction:u.KEEP}]])]],Rr=["title","aria-atomic","aria-autocomplete","aria-busy","aria-checked","aria-current","aria-disabled","aria-dropeffect","aria-expanded","aria-haspopup","aria-hidden","aria-invalid","aria-label","aria-level","aria-live","aria-multiline","aria-multiselectable","aria-orientation","aria-posinset","aria-pressed","aria-readonly","aria-relevant","aria-required","aria-selected","aria-setsize","aria-sort","aria-valuemax","aria-valuemin","aria-valuenow","aria-valuetext","alt","align","autocapitalize","autocomplete","autocorrect","autofocus","autoplay","bgcolor","border","cellpadding","cellspacing","checked","color","cols","colspan","controls","datetime","disabled","download","draggable","enctype","face","formenctype","frameborder","height","hreflang","hidden","ismap","label","lang","loop","max","maxlength","media","minlength","min","multiple","muted","nonce","open","placeholder","preload","rel","required","reversed","role","rows","rowspan","selected","shape","size","sizes","slot","span","spellcheck","start","step","summary","translate","type","valign","value","width","wrap","itemscope","itemtype","itemid","itemprop","itemref"],Ir=[["dir",{policyAction:u.KEEP_AND_NORMALIZE,conditions:new Map([["dir",new Set(["auto","ltr","rtl"])]])}],["async",{policyAction:u.KEEP_AND_NORMALIZE,conditions:new Map([["async",new Set(["async"])]])}],["cite",{policyAction:u.KEEP_AND_SANITIZE_URL}],["loading",{policyAction:u.KEEP_AND_NORMALIZE,conditions:new Map([["loading",new Set(["eager","lazy"])]])}],["poster",{policyAction:u.KEEP_AND_SANITIZE_URL}],["target",{policyAction:u.KEEP_AND_NORMALIZE,conditions:new Map([["target",new Set(["_self","_blank"])]])}]],Nt=new re(new Set(Pr),new Map(Ur),new Set(Rr),new Map(Ir))});var Ve=s(()=>{f();y();C();A();_t();$t();ke();ye();});var Ot=s(()=>{C();Ve();ke();ye();});function Fe(r,...e){if(e.length===0)return De(r[0]);let t=r[0].toLowerCase(),i=r[0];for(let n=0;n<e.length;n++)i+=encodeURIComponent(e[n])+r[n+1];return De(i)}var Ct=s(()=>{f();w();P();te();});var Pt=s(()=>{f();P();te();});var ie=s(()=>{f();C();});var Rt=s(()=>{f();te();ie();});var It=s(()=>{xt();vt();Ve();Ot();Ct();Pt();Rt();de();y();w();P();ie();});var zt=s(()=>{A();});var Lt=s(()=>{A();});var Dt=s(()=>{w();});var Mt=s(()=>{A();});var Ht=s(()=>{f();de();y();});var kt=s(()=>{w();});var Vt=s(()=>{A();});var Ft=s(()=>{y();w();});var jt=s(()=>{A();});var Wt=s(()=>{A();w();});var Bt=s(()=>{w();});var Ae={};xe(Ae,{setSrc:()=>Xr,setTextContent:()=>Jr});function Yr(r){var e;let t=r.document,i=(e=t.querySelector)===null||e===void 0?void 0:e.call(t,"script[nonce]");return i&&(i.nonce||i.getAttribute("nonce"))||""}function Gt(r){let e=r.ownerDocument&&r.ownerDocument.defaultView,t=Yr(e||window);t&&r.setAttribute("nonce",t)}function Jr(r,e,t){r.textContent=L(e),!t?.omitNonce&&Gt(r)}function Xr(r,e,t){r.src=E(e),!t?.omitNonce&&Gt(r)}var Kt=s(()=>{w();P();});var qt=s(()=>{ie();});var Zt=s(()=>{f();});var Yt=s(()=>{f();A();});var Jt=s(()=>{y();});var Xt=s(()=>{f();y();});var Qt=s(()=>{y();w();P();ie();});var er=s(()=>{P();Qt();});var tr=s(()=>{A();});var rr=s(()=>{w();});var ir=s(()=>{});var nr=s(()=>{A();});var or=s(()=>{w();});var sr=s(()=>{zt();Lt();Dt();Mt();Ht();kt();Vt();Ft();jt();Wt();Bt();Kt();qt();Zt();Yt();Jt();Xt();er();tr();He();rr();ir();nr();or();});var ar={};xe(ar,{createChartWrapper:()=>We,dataTable:()=>be,load:()=>je});async function je(r={}){await pi;let{version:e="current",packages:t=["corechart"],language:i=document.documentElement.lang||"en",mapsApiKey:n}=r;return google.charts.load(e,{packages:t,language:i,mapsApiKey:n})}async function be(r){if(await je(),r==null)return new google.visualization.DataTable;if(r.getNumberOfRows)return r;if(r.cols)return new google.visualization.DataTable(r);if(r.length>0)return google.visualization.arrayToDataTable(r);throw r.length===0?new Error("Data was empty."):new Error("Data format was not recognized.")}async function We(r){return await je(),new google.visualization.ChartWrapper({container:r})}var pi,Be=s(()=>{It();sr();pi=new Promise((r,e)=>{if(typeof google<"u"&&google.charts&&typeof google.charts.load=="function")r();else{let t=document.querySelector('script[src="https://www.gstatic.com/charts/loader.js"]');t||(t=document.createElement("script"),Ae.setSrc(t,Fe`https://www.gstatic.com/charts/loader.js`),document.head.appendChild(t)),t.addEventListener("load",r),t.addEventListener("error",e)}})});var cr={};xe(cr,{GoogleChart:()=>g});var T,lr,ui,g,pr=s(()=>{dt();At();Be();T=function(r,e,t,i){var n=arguments.length,o=n<3?e:i===null?i=Object.getOwnPropertyDescriptor(e,t):i,a;if(typeof Reflect=="object"&&typeof Reflect.decorate=="function")o=Reflect.decorate(r,e,t,i);else for(var c=r.length-1;c>=0;c--)(a=r[c])&&(o=(n<3?a(o):n>3?a(e,t,o):a(e,t))||o);return n>3&&o&&Object.defineProperty(e,t,o),o},lr=["ready","select"],ui={area:"AreaChart",bar:"BarChart","md-bar":"google.charts.Bar",bubble:"BubbleChart",calendar:"Calendar",candlestick:"CandlestickChart",column:"ColumnChart",combo:"ComboChart",gantt:"Gantt",gauge:"Gauge",geo:"GeoChart",histogram:"Histogram",line:"LineChart","md-line":"google.charts.Line",org:"OrgChart",pie:"PieChart",sankey:"Sankey",scatter:"ScatterChart","md-scatter":"google.charts.Scatter","stepped-area":"SteppedAreaChart",table:"Table",timeline:"Timeline",treemap:"TreeMap",wordtree:"WordTree"},g=class extends N{constructor(){super(...arguments),this.type="column",this.events=[],this.options=void 0,this.cols=void 0,this.rows=void 0,this.data=void 0,this.view=void 0,this.selection=void 0,this.drawn=!1,this._data=void 0,this.chartWrapper=null,this.redrawTimeoutId=void 0}render(){return at`
      <div id="styles"></div>
      <div id="chartdiv"></div>
    `}firstUpdated(){We(this.shadowRoot.getElementById("chartdiv")).then(e=>{this.chartWrapper=e,this.typeChanged(),google.visualization.events.addListener(e,"ready",()=>{this.drawn=!0,this.selection&&this.selectionChanged()}),google.visualization.events.addListener(e,"select",()=>{this.selection=e.getChart().getSelection()}),this.propagateEvents(lr,e)})}updated(e){e.has("type")&&this.typeChanged(),(e.has("rows")||e.has("cols"))&&this.rowsOrColumnsChanged(),e.has("data")&&this.dataChanged(),e.has("view")&&this.viewChanged(),(e.has("_data")||e.has("options"))&&this.redraw(),e.has("selection")&&this.selectionChanged()}typeChanged(){if(this.chartWrapper==null)return;this.chartWrapper.setChartType(ui[this.type]||this.type);let e=this.chartWrapper.getChart();google.visualization.events.addOneTimeListener(this.chartWrapper,"ready",()=>{let t=this.chartWrapper.getChart();t!==e&&this.propagateEvents(this.events.filter(n=>!lr.includes(n)),t);let i=this.shadowRoot.getElementById("styles");i.children.length||this.localizeGlobalStylesheets(i)}),this.redraw()}propagateEvents(e,t){for(let i of e)google.visualization.events.addListener(t,i,n=>{this.dispatchEvent(new CustomEvent(`google-chart-${i}`,{bubbles:!0,composed:!0,detail:{chart:this.chartWrapper.getChart(),data:n}}))})}selectionChanged(){if(this.chartWrapper==null)return;let e=this.chartWrapper.getChart();if(e!=null&&e.setSelection){if(this.type==="timeline"){let t=JSON.stringify(e.getSelection());if(JSON.stringify(this.selection)===t)return}e.setSelection(this.selection)}}redraw(){this.chartWrapper==null||this._data==null||(this.chartWrapper.setDataTable(this._data),this.chartWrapper.setOptions(this.options||{}),this.drawn=!1,this.redrawTimeoutId!==void 0&&clearTimeout(this.redrawTimeoutId),this.redrawTimeoutId=window.setTimeout(()=>{this.chartWrapper.draw()},5))}get imageURI(){if(this.chartWrapper==null)return null;let e=this.chartWrapper.getChart();return e&&e.getImageURI()}viewChanged(){this.view&&(this._data=this.view)}async rowsOrColumnsChanged(){let{rows:e,cols:t}=this;if(!(!e||!t))try{let i=await be({cols:t});i.addRows(e),this._data=i}catch(i){this.shadowRoot.getElementById("chartdiv").textContent=String(i)}}dataChanged(){let e=this.data,t;if(!e)return;let i=!1;try{e=JSON.parse(e)}catch{i=typeof e=="string"||e instanceof String}i?t=fetch(e).then(n=>n.json()):t=Promise.resolve(e),t.then(be).then(n=>{this._data=n})}localizeGlobalStylesheets(e){let t=Array.from(document.head.querySelectorAll('link[rel="stylesheet"][type="text/css"][id^="load-css-"]'));for(let i of t){let n=document.createElement("link");n.setAttribute("rel","stylesheet"),n.setAttribute("type","text/css"),n.setAttribute("href",i.getAttribute("href")),e.appendChild(n)}}};g.styles=ve`
    :host {
      display: -webkit-flex;
      display: -ms-flex;
      display: flex;
      margin: 0;
      padding: 0;
      width: 400px;
      height: 300px;
    }

    :host([hidden]) {
      display: none;
    }

    :host([type="gauge"]) {
      width: 300px;
      height: 300px;
    }

    #chartdiv {
      width: 100%;
    }

    /* Workaround for slow initial ready event for tables. */
    .google-visualization-table-loadtest {
      padding-left: 6px;
    }
  `;T([S({type:String,reflect:!0})],g.prototype,"type",void 0);T([S({type:Array})],g.prototype,"events",void 0);T([S({type:Object,hasChanged:()=>!0})],g.prototype,"options",void 0);T([S({type:Array})],g.prototype,"cols",void 0);T([S({type:Array})],g.prototype,"rows",void 0);T([S({type:String})],g.prototype,"data",void 0);T([S({type:Object})],g.prototype,"view",void 0);T([S({type:Array})],g.prototype,"selection",void 0);T([S({type:Object})],g.prototype,"_data",void 0);customElements.define("google-chart",g)});var ur=!1,ne=null,di=async()=>{if(ur)return;ur=!0,await Promise.resolve().then(()=>(pr(),cr)),ne=(await Promise.resolve().then(()=>(Be(),ar))).dataTable};function hi({type:r,options:e,cachedData:t}){return{chart:null,init:async function(){await di();let i=await this.initChart();this.$wire.on("updateChart",async({data:n})=>{ne&&(i.data=await ne(n))})},initChart:async function(i=null){if(this.chart=this.$refs.googleChart,this.chart.type=r,this.chart.options=e??{},ne){let n=await ne(i??t);this.chart.data=n}return this.chart}}}export{hi as default};
/*! Bundled license information:

@lit/reactive-element/css-tag.js:
  (**
   * @license
   * Copyright 2019 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/reactive-element.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

lit-html/lit-html.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

lit-element/lit-element.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

lit-html/is-server.js:
  (**
   * @license
   * Copyright 2022 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/custom-element.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/property.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/state.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/event-options.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/base.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/query.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/query-all.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/query-async.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/query-assigned-elements.js:
  (**
   * @license
   * Copyright 2021 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

@lit/reactive-element/decorators/query-assigned-nodes.js:
  (**
   * @license
   * Copyright 2017 Google LLC
   * SPDX-License-Identifier: BSD-3-Clause
   *)

safevalues/dist/mjs/environment/dev.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/secrets.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/attribute_impl.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/string_literal.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/sensitive_attributes.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/attribute_builders.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/trusted_types.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/html_impl.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/resource_url_impl.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/script_impl.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/html_builders.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/url_sanitizer.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/range.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/html_sanitizer/inert_fragment.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/html_sanitizer/no_clobber.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/html_sanitizer/sanitizer_table/sanitizer_table.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/html_sanitizer/sanitizer_table/default_sanitizer_table.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/html_sanitizer/html_sanitizer.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/html_sanitizer/html_sanitizer_builder.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/resource_url_builders.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/script_builders.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/internals/style_sheet_impl.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/builders/style_sheet_builders.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/index.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/anchor.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/area.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/base.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/button.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/element.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/embed.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/form.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/iframe.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/input.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/link.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/object.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/script.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/style.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/svg.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/elements/svg_use.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/document.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/dom_parser.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/fetch.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/global.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/location.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/service_worker_container.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/url.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/window.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/globals/worker.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

safevalues/dist/mjs/dom/index.js:
  (**
   * @license
   * SPDX-License-Identifier: Apache-2.0
   *)

@google-web-components/google-chart/loader.js:
  (**
   * @license
   * Copyright 2014-2020 Google LLC
   *
   * Licensed under the Apache License, Version 2.0 (the "License");
   * you may not use this file except in compliance with the License.
   * You may obtain a copy of the License at
   *
   *     https://www.apache.org/licenses/LICENSE-2.0
   *
   * Unless required by applicable law or agreed to in writing, software
   * distributed under the License is distributed on an "AS IS" BASIS,
   * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   * See the License for the specific language governing permissions and
   * limitations under the License.
   *)

@google-web-components/google-chart/google-chart.js:
  (**
   * @license
   * Copyright 2014-2020 Google LLC
   *
   * Licensed under the Apache License, Version 2.0 (the "License");
   * you may not use this file except in compliance with the License.
   * You may obtain a copy of the License at
   *
   *     https://www.apache.org/licenses/LICENSE-2.0
   *
   * Unless required by applicable law or agreed to in writing, software
   * distributed under the License is distributed on an "AS IS" BASIS,
   * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   * See the License for the specific language governing permissions and
   * limitations under the License.
   *)
*/
