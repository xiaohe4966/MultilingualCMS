import{c as r,a4 as i,e as a,v as p,f as c,s as u,R as m,o as d,A as f,B as y,h as _,n as x,k as S,l as g,D as v,_ as C,$ as z}from"./index-eK2_OH1N.js";const b=r({type:{type:String,values:["primary","success","info","warning","danger",""],default:""},size:{type:String,values:i,default:""},truncated:Boolean,lineClamp:{type:[String,Number]},tag:{type:String,default:"span"}}),h=a({name:"ElText"}),k=a({...h,props:b,setup(n){const t=n,l=p(),e=c("text"),o=u(()=>[e.b(),e.m(t.type),e.m(l.value),e.is("truncated",t.truncated),e.is("line-clamp",!m(t.lineClamp))]);return(s,B)=>(d(),f(v(s.tag),{class:x(S(o)),style:g({"-webkit-line-clamp":s.lineClamp})},{default:y(()=>[_(s.$slots,"default")]),_:3},8,["class","style"]))}});var w=C(k,[["__file","text.vue"]]);const T=z(w);export{T as E};