import React from 'react'
import PropTypes from 'prop-types'
import { Helmet } from 'react-helmet-async'

import { useDbQuery } from 'gatsby-theme-docz/src/hooks/useDbQuery'
import {graphql, useStaticQuery} from "gatsby";

const SEO = ({ description, lang, meta, keywords, title: initialTitle }) => {
    const db = useDbQuery();
    const title = initialTitle || db.config.title;
    const metaInfo = useStaticQuery(graphql`query MyQuery {
        allMdx {
            nodes {
                mdxAST
                frontmatter {
                    route
                    name
                }
            }
        }
    }`);
    const methodFirstPar = metaInfo.allMdx.nodes
        .filter( (node) => node.frontmatter.name === title && node.frontmatter.route.startsWith("method/"))
        .map ((node) => node.mdxAST.children.filter( (child) => child.type === 'paragraph' )[0].children[0].value);
    const metaDescription = (methodFirstPar.length === 0) ? db.config.description : "VK API method " + title + ": " + methodFirstPar[0];
    (methodFirstPar.length !== 0) && (keywords = [title]);
    return (
        <Helmet
            title={title}
            titleTemplate={`%s | ${db.config.title}`}
            htmlAttributes={{ lang }}
            meta={[
                {
                    name: `description`,
                    content: metaDescription,
                },
                {
                    property: `og:title`,
                    content: title,
                },
                {
                    property: `og:description`,
                    content: metaDescription,
                },
                {
                    property: `og:type`,
                    content: `website`,
                },
                {
                    name: `twitter:card`,
                    content: `summary`,
                },
                {
                    name: `twitter:title`,
                    content: title,
                },
                {
                    name: `twitter:description`,
                    content: metaDescription,
                },
            ]
                .concat(
                    keywords.length > 0
                        ? {
                            name: `keywords`,
                            content: keywords.join(`, `),
                        }
                        : []
                )
                .concat(meta)}
        />
    )
}

SEO.defaultProps = {
    lang: `en`,
    meta: [],
    keywords: [],
}

SEO.propTypes = {
    description: PropTypes.string,
    lang: PropTypes.string,
    meta: PropTypes.array,
    keywords: PropTypes.arrayOf(PropTypes.string),
    title: PropTypes.string.isRequired,
}

export default SEO
