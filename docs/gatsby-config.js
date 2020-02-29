module.exports = {
    pathPrefix: "/vk-audio-token",
    plugins: [
        {
            resolve: 'gatsby-theme-docz',
            options: {
                gatsbyRemarkPlugins: [{ resolve: "../../../src/plugins/source-template-plugin" }]
            },
        }
    ]
};