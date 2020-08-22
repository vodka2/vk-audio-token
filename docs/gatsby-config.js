module.exports = {
    pathPrefix: "/vk-audio-token",
    siteMetadata: {
        siteUrl: `https://vodka2.github.io`,
    },
    plugins: [
        {
            resolve: 'gatsby-theme-docz',
            options: {
                gatsbyRemarkPlugins: [{ resolve: "../../../src/plugins/source-template-plugin" }],
                src: "src",
                mdxExtensions: [".mdx"],
                title: 'VK Audio API reference for VK Audio Token',
                description: 'Description of VK API methods that can be used with the VK Audio Token package'
            },
        },
        {
            resolve: 'gatsby-plugin-sitemap',
            options: {
                query:
                    `{
                    site {
                        siteMetadata {
                          siteUrl
                        }
                    }
                    allSitePage {
                        edges {
                          node {
                            path
                          }
                        }
                    }
                    }`,
                serialize: ({ site, allSitePage }) => {
                    const lastMod = (new Date()).getFullYear() + "-" + ((new Date()).getMonth() + 1) + "-" + (new Date()).getDate();
                    return allSitePage.edges.map(edge => {
                        return {
                            url: `${site.siteMetadata.siteUrl}${edge.node.path}`,
                            changefreq: `monthly`,
                            lastmod: lastMod
                        }
                    })
                }
            }
        }
    ]
};