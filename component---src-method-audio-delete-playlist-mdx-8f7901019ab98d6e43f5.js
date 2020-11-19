(window.webpackJsonp=window.webpackJsonp||[]).push([[10],{awuM:function(e,t,n){"use strict";n.r(t),n.d(t,"_frontmatter",(function(){return o})),n.d(t,"default",(function(){return p}));n("91GP"),n("rGqo"),n("yt8O"),n("Btvt"),n("RW0V"),n("FlsD"),n("q1tI");var a=n("E/Ix"),l=n("hhGP");n("qKvR");function r(){return(r=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var a in n)Object.prototype.hasOwnProperty.call(n,a)&&(e[a]=n[a])}return e}).apply(this,arguments)}var o={};void 0!==o&&o&&o===Object(o)&&Object.isExtensible(o)&&!o.hasOwnProperty("__filemeta")&&Object.defineProperty(o,"__filemeta",{configurable:!0,value:{name:"_frontmatter",filename:"src/method/audio.deletePlaylist.mdx"}});var i={_frontmatter:o},c=l.a;function p(e){var t=e.components,n=function(e,t){if(null==e)return{};var n,a,l={},r=Object.keys(e);for(a=0;a<r.length;a++)n=r[a],t.indexOf(n)>=0||(l[n]=e[n]);return l}(e,["components"]);return Object(a.b)(c,r({},i,n,{components:t,mdxType:"MDXLayout"}),Object(a.b)("h1",{id:"audiodeleteplaylist"},"audio.deletePlaylist"),Object(a.b)("p",null,"Delete a playlist from the audios for a specified user or community"),Object(a.b)("p",null,"Parameters:"),Object(a.b)("table",null,Object(a.b)("thead",{parentName:"table"},Object(a.b)("tr",{parentName:"thead"},Object(a.b)("th",r({parentName:"tr"},{align:null}),"Name"),Object(a.b)("th",r({parentName:"tr"},{align:null}),"Value"))),Object(a.b)("tbody",{parentName:"table"},Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",r({parentName:"tr"},{align:null}),"owner_id"),Object(a.b)("td",r({parentName:"tr"},{align:null}),"User or community id (for example, obtained by users.get)")),Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",r({parentName:"tr"},{align:null}),"playlist_id"),Object(a.b)("td",r({parentName:"tr"},{align:null}),"Playlist id")))),Object(a.b)("p",null,"Example (Kate):"),Object(a.b)("pre",null,Object(a.b)("code",r({parentName:"pre"},{className:"language-php"}),'\n$ownerId = 1;\n$playlistId = 1;\n\ncurl_setopt(\n    $ch,\n    CURLOPT_URL,\n    "https://api.vk.com/method/audio.deletePlaylist?access_token=".TOKEN.\n    "&owner_id=$ownerId&playlist_id=$playlistId&v=5.95"\n);\n\n')),Object(a.b)("pre",null,Object(a.b)("code",r({parentName:"pre"},{className:"language-php"}),"<?php\n\ninclude __DIR__.'/../../autoloader.php';\n\nuse Vodka2\\VKAudioToken\\SupportedClients;\n\n//Token obtained by example_microg.php script\ndefine('TOKEN', $argv[1]);\ndefine('USER_AGENT', SupportedClients::Kate()->getUserAgent());\n$ch = curl_init();\n\ncurl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: '.USER_AGENT));\ncurl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);\n\n$ownerId = 1;\n$playlistId = 1;\n\ncurl_setopt(\n    $ch,\n    CURLOPT_URL,\n    \"https://api.vk.com/method/audio.deletePlaylist?access_token=\".TOKEN.\n    \"&owner_id=$ownerId&playlist_id=$playlistId&v=5.95\"\n);\n\necho json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).\"\\n\\n\";\n")))}void 0!==p&&p&&p===Object(p)&&Object.isExtensible(p)&&!p.hasOwnProperty("__filemeta")&&Object.defineProperty(p,"__filemeta",{configurable:!0,value:{name:"MDXContent",filename:"src/method/audio.deletePlaylist.mdx"}}),p.isMDXComponent=!0}}]);
//# sourceMappingURL=component---src-method-audio-delete-playlist-mdx-8f7901019ab98d6e43f5.js.map