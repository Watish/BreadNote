(this.webpackJsonpleanote=this.webpackJsonpleanote||[]).push([[0],{123:function(e,t,n){},150:function(e,t){},152:function(e,t,n){"use strict";n.r(t);var a=n(0),r=n(24),c=n.n(r),i=n(85),s=n(69),o=n(39);var l=Object(s.a)({TagList:function(e,t){(void 0===e||1===e.length&&""===e[0])&&(e=[]);var n=t.type,a=t.TagListOrSingleTag;switch(console.log("TagListOrSingleTag",a),n){case"delete":return e.filter((function(e){return e!==a}));case"add":return console.log("add",[].concat(Object(o.a)(e),Object(o.a)(a))),[].concat(Object(o.a)(e),Object(o.a)(a));case"clear":return[];default:return e}}}),u=Object(s.b)(l),d=n(29),j=n(30),b=n(31),g=n(32),h=n(8),p=n(33),f=n.n(p),O=n(47),m=n.p+"static/media/bg.18c452c7.jpg",v=n(80),x=n(48),w=n.n(x),k=(n(123),n(110)),y=n(109),N=n(156),P=n(3),C=function(e){Object(b.a)(n,e);var t=Object(g.a)(n);function n(){var e;Object(d.a)(this,n);for(var a=arguments.length,r=new Array(a),c=0;c<a;c++)r[c]=arguments[c];return(e=t.call.apply(t,[this].concat(r))).state={active:"login",loginEmail:"",loginPassword:"",registerEmail:"",registerPassword:""},e.handleForm=function(){return Object(O.a)(f.a.mark((function t(){var n,a,r,c,i;return f.a.wrap((function(t){for(;;)switch(t.prev=t.next){case 0:n=e.state,a=n.active,r=n.loginEmail,c=n.loginPassword,n.registerEmail,n.registerPassword,t.t0=a,t.next="login"===t.t0?4:"register"===t.t0?18:20;break;case 4:return t.prev=4,k.b.loading({content:"\u767b\u9646\u4e2d...",key:"loading"}),t.next=8,Object(v.a)("auth/login",{email:r,pwd:c},"post");case 8:i=t.sent,console.log(i),i.Ok?(w.a.publish("toBreadNote",{token:i.Token}),k.b.success({content:"\u767b\u5f55\u6210\u529f!",key:"loading",duration:2})):k.b.warn({content:"\u5bc6\u7801\u9519\u8bef\u6216\u7528\u6237\u540d\u4e0d\u5b58\u5728",key:"loading",duration:2}),t.next=17;break;case 13:t.prev=13,t.t1=t.catch(4),console.log(t.t1),k.b.warn({content:"\u767b\u9646\u5931\u8d25",key:"loading",duration:2});case 17:return t.abrupt("break",20);case 18:return y.a.info({message:"\u63d0\u793a",description:"\u76ee\u524d\u8be5\u9879\u76ee\u5904\u4e8e\u6d4b\u8bd5\u72b6\u6001,\u6682\u4e0d\u652f\u6301\u8d26\u53f7\u7684\u6ce8\u518c",icon:Object(P.jsx)(N.a,{})}),t.abrupt("break",20);case 20:case"end":return t.stop()}}),t,null,[[4,13]])})))},e}return Object(j.a)(n,[{key:"componentWillUnmount",value:function(){this.setState({},(function(){return!1}))}},{key:"render",value:function(){var e=this,t=this.state,n=t.active,a=t.loginEmail,r=t.loginPassword,c=t.registerEmail,i=t.registerPassword;return Object(P.jsx)("div",{className:"Login_Container",children:Object(P.jsxs)("div",{className:"container",children:[Object(P.jsx)("img",{src:m}),Object(P.jsx)("div",{className:"panel",children:Object(P.jsxs)("div",{className:"content login",children:[Object(P.jsxs)("div",{className:"switch",children:[Object(P.jsx)("span",{onClick:function(){e.setState({active:"login"})},className:"".concat("login"===n?"active":""),children:"\u767b\u9646"}),Object(P.jsx)("span",{children:"/"}),Object(P.jsx)("span",{onClick:function(){e.setState({active:"register"})},className:"".concat("register"===n?"active":""),children:"\u6ce8\u518c"})]}),Object(P.jsxs)("div",{className:"form",children:["register"===n?Object(P.jsxs)("div",{children:[Object(P.jsxs)("div",{className:"input",children:[Object(P.jsx)("input",{type:"text",className:"".concat(c||a?"hasValue":""),ref:function(t){return e.registerEmail=t},onChange:function(){return e.setState({registerEmail:e.registerEmail.value})}}),Object(P.jsx)("label",{children:"\u90ae\u7bb1"})]}),Object(P.jsxs)("div",{className:"input",children:[Object(P.jsx)("input",{type:"password",className:"".concat(i||r?"hasValue":""),ref:function(t){return e.registerPassword=t},onChange:function(){return e.setState({registerPassword:e.registerPassword.value})}}),Object(P.jsx)("label",{children:"\u5bc6\u7801"})]})]}):Object(P.jsxs)("div",{children:[Object(P.jsxs)("div",{className:"input",children:[Object(P.jsx)("input",{type:"text",className:"".concat(a||c?"hasValue":""),ref:function(t){return e.loginEmail=t},onChange:function(){return e.setState({loginEmail:e.loginEmail.value})}}),Object(P.jsx)("label",{children:"\u90ae\u7bb1"})]}),Object(P.jsxs)("div",{className:"input",children:[Object(P.jsx)("input",{type:"password",className:"".concat(r||i?"hasValue":""),ref:function(t){return e.loginPassword=t},onChange:function(){return e.setState({loginPassword:e.loginPassword.value})}}),Object(P.jsx)("label",{children:"\u5bc6\u7801"})]})]}),Object(P.jsx)("button",{type:"button",onClick:this.handleForm(),children:"login"===n?"\u767b\u5f55":"\u6ce8\u518c"})]})]})})]})})}}]),n}(a.Component),E=C,S=Object(a.lazy)((function(){return Promise.all([n.e(2),n.e(4)]).then(n.bind(null,568))})),L=function(e){Object(b.a)(n,e);var t=Object(g.a)(n);function n(){return Object(d.a)(this,n),t.apply(this,arguments)}return Object(j.a)(n,[{key:"componentDidMount",value:function(){var e=this;w.a.subscribe("toBreadNote",(function(t,n){e.props.history.replace("/BreadNote",n)}))}},{key:"render",value:function(){return Object(P.jsx)("div",{children:Object(P.jsxs)(a.Suspense,{fallback:E,children:[Object(P.jsx)(h.b,{path:"/userLogin",component:E}),Object(P.jsx)(h.b,{path:"/BreadNote",component:S}),Object(P.jsx)(h.a,{to:"/userLogin"})]})})}}]),n}(a.Component),T=Object(h.f)(L),B=n(68),F=(n(121),function(e){Object(b.a)(n,e);var t=Object(g.a)(n);function n(){return Object(d.a)(this,n),t.apply(this,arguments)}return Object(j.a)(n,[{key:"render",value:function(){return Object(P.jsx)("div",{children:Object(P.jsx)(B.a,{children:Object(P.jsx)(T,{})})})}}]),n}(a.Component)),V=F,I=function(e){e&&e instanceof Function&&n.e(5).then(n.bind(null,562)).then((function(t){var n=t.getCLS,a=t.getFID,r=t.getFCP,c=t.getLCP,i=t.getTTFB;n(e),a(e),r(e),c(e),i(e)}))};c.a.render(Object(P.jsx)(i.a,{store:u,children:Object(P.jsx)(V,{})}),document.getElementById("root")),I()},80:function(e,t,n){"use strict";n.d(t,"a",(function(){return u})),n.d(t,"b",(function(){return d}));var a=n(33),r=n.n(a),c=n(47),i=n(107),s=n.n(i),o=n(108),l=n.n(o),u=function(e){var t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:{},n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"post";return new Promise((function(a,r){t=l.a.stringify(t,{encoder:function(e,t,n,a){if("value"===a||"key"===a)return encodeURIComponent(e)}}),s.a.request({url:"/api/".concat(e),data:t,method:n,timeout:1e4}).then((function(e){a(e.data)}),(function(e){r({status:!1})}))}))},d=function(e){var t=e.map(function(){var e=Object(c.a)(r.a.mark((function e(t){return r.a.wrap((function(e){for(;;)switch(e.prev=e.next){case 0:return e.next=2,u(t.url,t.data,t.method);case 2:return e.abrupt("return",e.sent);case 3:case"end":return e.stop()}}),e)})));return function(t){return e.apply(this,arguments)}}());return new Promise((function(e,n){Promise.all(t).then((function(t){e(t)}),(function(e){n({status:!1})}))}))}}},[[152,1,3]]]);
//# sourceMappingURL=main.da4c04f2.chunk.js.map