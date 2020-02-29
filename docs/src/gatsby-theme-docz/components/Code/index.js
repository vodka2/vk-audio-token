/** @jsx jsx */
/* eslint react/jsx-key: 0 */
import Highlight, { defaultProps } from 'prism-react-renderer'
import { jsx, Styled } from 'theme-ui'

import { usePrismTheme } from '~utils/theme'

export const Code = ({ children, className: outerClassName }) => {
  const [language] = outerClassName
    ? outerClassName.replace(/language-/, '').split(' ')
    : ['text'];
  const theme = usePrismTheme();
  const isFull = children.trim().indexOf('<?php') !== -1;
  return (
    <div>
      {(isFull) && <button onClick={(data) => {
        const next = data.target.nextSibling;
        if (next.style.display !== 'none') {
          next.style.display = 'none';
          data.target.innerHTML = 'Show full code';
        } else {
          next.style.display = '';
          data.target.innerHTML = 'Hide full code';
        }
      }}>Show full code
      </button>}
    <div style={{display: (isFull) ? 'none' : ''}}>
    <Highlight
      {...defaultProps}
      code={children.trim()}
      language={language}
      theme={theme}
    >
      {({ className, style, tokens, getLineProps, getTokenProps }) => (
        <Styled.pre
          className={`${outerClassName || ''} ${className}`}
          style={{ ...style, overflowX: 'auto', margin: 0 }}
          data-testid="code"
        >
          {tokens.map((line, i) => (
            <div {...getLineProps({ line, key: i })}>
              {line.map((token, key) => (
                <span
                  {...((t) => {if (t.children === "" && line.length === 1) {t.children = '\n';}  return t;})
                      .call(null, getTokenProps({ token, key }))}
                  sx={{ display: 'inline-block' }}
                />
              ))}
            </div>
          ))}
        </Styled.pre>
      )}
    </Highlight>
    </div>
    </div>
  )
};
