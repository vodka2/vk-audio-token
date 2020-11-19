const visit = require('unist-util-visit-parents');
var u = require('unist-builder');

const kateCommonStart = `<?php

include __DIR__.'/../../autoloader.php';

use Vodka2\\VKAudioToken\\SupportedClients;

//Token obtained by example_microg.php script
define('TOKEN', $argv[1]);
define('USER_AGENT', SupportedClients::Kate()->getUserAgent());
$ch = curl_init();

curl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: '.USER_AGENT));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
`;

const kateCommonEnd = `
echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\\n\\n";`;

const vkOfficialCommonStart = `<?php

include __DIR__.'/../../autoloader.php';

use Vodka2\\VKAudioToken\\SupportedClients;

//Credentials obtained by example_vkofficial.php script
define('TOKEN', $argv[1]);
define('USER_AGENT', SupportedClients::VkOfficial()->getUserAgent());
$ch = curl_init();

curl_setopt($ch,CURLOPT_HTTPHEADER, array('User-Agent: '.USER_AGENT));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
`;

const vkOfficialCommonEnd = `
echo json_encode(json_decode(curl_exec($ch)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\\n\\n";`;

module.exports = ({markdownAST}) => {
    visit(markdownAST, 'code', (node, parents) => {
        if (node.value.startsWith("//tmpl")) {
            const parent = parents[0];
            const index = parent.children.indexOf(node) + 1;
            parent.children.splice(index, 0,
                u('code', {
                    lang: node.lang,
                    meta: node.meta,
                    value:
                        node.value
                            .replace('//tmpl-kate-common-start', kateCommonStart)
                            .replace('//tmpl-kate-common-end', kateCommonEnd)
                            .replace('//tmpl-vkoff-common-start', vkOfficialCommonStart)
                            .replace('//tmpl-vkoff-common-end', vkOfficialCommonEnd)
                })
            );
            node.value = node.value.replace(/\/\/tmpl-.+/g, '');
        }
    });
    return markdownAST
};