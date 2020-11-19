(window.webpackJsonp=window.webpackJsonp||[]).push([[40],{"6wv+":function(e,t,n){"use strict";n.r(t),n.d(t,"_frontmatter",(function(){return r})),n.d(t,"default",(function(){return c}));n("91GP"),n("rGqo"),n("yt8O"),n("Btvt"),n("RW0V"),n("FlsD"),n("q1tI");var a=n("E/Ix"),i=n("hhGP");n("qKvR");function l(){return(l=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var a in n)Object.prototype.hasOwnProperty.call(n,a)&&(e[a]=n[a])}return e}).apply(this,arguments)}var r={};void 0!==r&&r&&r===Object(r)&&Object.isExtensible(r)&&!r.hasOwnProperty("__filemeta")&&Object.defineProperty(r,"__filemeta",{configurable:!0,value:{name:"_frontmatter",filename:"src/newmethod/execute.savePlaylist.mdx"}});var o={_frontmatter:r},d=i.a;function c(e){var t=e.components,n=function(e,t){if(null==e)return{};var n,a,i={},l=Object.keys(e);for(a=0;a<l.length;a++)n=l[a],t.indexOf(n)>=0||(i[n]=e[n]);return i}(e,["components"]);return Object(a.b)(d,l({},o,n,{components:t,mdxType:"MDXLayout"}),Object(a.b)("h1",{id:"executesaveplaylist"},"execute.savePlaylist"),Object(a.b)("p",null,"Create or edit playlist"),Object(a.b)("p",null,"Parameters:"),Object(a.b)("table",null,Object(a.b)("thead",{parentName:"table"},Object(a.b)("tr",{parentName:"thead"},Object(a.b)("th",l({parentName:"tr"},{align:null}),"Name"),Object(a.b)("th",l({parentName:"tr"},{align:null}),"Value"))),Object(a.b)("tbody",{parentName:"table"},Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",l({parentName:"tr"},{align:null}),"playlist_id"),Object(a.b)("td",l({parentName:"tr"},{align:null}),"Id of the playlist. If 0 a new playlist is created")),Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",l({parentName:"tr"},{align:null}),"owner_id"),Object(a.b)("td",l({parentName:"tr"},{align:null}),"Id of the owner of the playlist")),Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",l({parentName:"tr"},{align:null}),"title"),Object(a.b)("td",l({parentName:"tr"},{align:null}),"Playlist title")),Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",l({parentName:"tr"},{align:null}),"description"),Object(a.b)("td",l({parentName:"tr"},{align:null}),"Playlist description")),Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",l({parentName:"tr"},{align:null}),"audio_ids_to_add (optional)"),Object(a.b)("td",l({parentName:"tr"},{align:null}),"Comma separated list of audio ids — owner_id, audio_id, access_key (optional), i.e. ",Object(a.b)("inlineCode",{parentName:"td"},"-123_123_abc123"))),Object(a.b)("tr",{parentName:"tbody"},Object(a.b)("td",l({parentName:"tr"},{align:null}),"reorder_actions (optional)"),Object(a.b)("td",l({parentName:"tr"},{align:null}),"JSON array, where each element is JSON array of owner_id, audio_id, new position in playlist")))),Object(a.b)("p",null,"Access keys in ",Object(a.b)("inlineCode",{parentName:"p"},"audio_ids_to_add"),' may be needed where audio owner id starts with "-".'),Object(a.b)("p",null,Object(a.b)("inlineCode",{parentName:"p"},"reorder_actions")," parameter is needed to replay reordering on the server. For example, ",Object(a.b)("inlineCode",{parentName:"p"},"[[123,456,2],[678,901,0]]"),'\nmeans "make 123_456 the third element, then make 678_901 the first element of the playlist".'),Object(a.b)("p",null,"Example of creating a new playlist (VK Official):"),Object(a.b)("pre",null,Object(a.b)("code",l({parentName:"pre"},{className:"language-php"}),'\n$title = \'Playlist title\';\n$description = \'Playlist description\';\n$owner_id = 12345;\n$playlist_id = 0;\n$audio_ids_to_add = \'12345_1333_abc123,56789_2444_def567\';\n\ncurl_setopt(\n    $ch, CURLOPT_URL, "https://api.vk.com/method/execute.savePlaylist"\n);\ncurl_setopt($ch, CURLOPT_POSTFIELDS,\n    "v=5.116&https=1&title=".urlencode($title)."&description=".urlencode($description).\n    "&audio_ids_to_add=".urlencode($audio_ids_to_add)."&playlist_id=${playlist_id}&owner_id=${owner_id}".\n    "&lang=en&access_token=".TOKEN\n);\n\n')),Object(a.b)("pre",null,Object(a.b)("code",l({parentName:"pre"},{className:"language-php"}),"<?php\n\ninclude __DIR__.'/../../autoloader.php';\n\nuse Vodka2\\VKAudioToken\\SupportedClients;\n\n//Credentials obtained by example_vkofficial.php script\ndefine('TOKEN', $argv[1]);\ndefine('USER_AGENT', SupportedClients::VkOfficial()->getUserAgent());\n$ch = curl_init();\n\ncurl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: '.USER_AGENT));\ncurl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);\ncurl_setopt($ch, CURLOPT_POST, 1);\n\n$title = 'Playlist title';\n$description = 'Playlist description';\n$owner_id = 12345;\n$playlist_id = 0;\n$audio_ids_to_add = '12345_1333_abc123,56789_2444_def567';\n\ncurl_setopt(\n    $ch, CURLOPT_URL, \"https://api.vk.com/method/execute.savePlaylist\"\n);\ncurl_setopt($ch, CURLOPT_POSTFIELDS,\n    \"v=5.116&https=1&title=\".urlencode($title).\"&description=\".urlencode($description).\n    \"&audio_ids_to_add=\".urlencode($audio_ids_to_add).\"&playlist_id=${playlist_id}&owner_id=${owner_id}\".\n    \"&lang=en&access_token=\".TOKEN\n);\n\necho json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).\"\\n\\n\";\n")))}void 0!==c&&c&&c===Object(c)&&Object.isExtensible(c)&&!c.hasOwnProperty("__filemeta")&&Object.defineProperty(c,"__filemeta",{configurable:!0,value:{name:"MDXContent",filename:"src/newmethod/execute.savePlaylist.mdx"}}),c.isMDXComponent=!0}}]);
//# sourceMappingURL=component---src-newmethod-execute-save-playlist-mdx-147ee6c7c8f64afd43ab.js.map