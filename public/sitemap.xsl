<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9">
    <xsl:output method="html" indent="yes"/>
    <xsl:template match="/">
        <html>
            <head>
                <title>XML Sitemap</title>
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; background: #f8fafc; }
                    .container { max-width: 1000px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
                    h1 { color: #1f2937; margin-bottom: 8px; }
                    .subtitle { color: #6b7280; margin-bottom: 32px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
                    th { background: #f9fafb; font-weight: 600; color: #374151; }
                    tr:hover { background: #f9fafb; }
                    a { color: #3b82f6; text-decoration: none; }
                    a:hover { text-decoration: underline; }
                    .info { background: #dbeafe; border: 1px solid #93c5fd; border-radius: 6px; padding: 16px; margin-bottom: 24px; color: #1e40af; }
                    .priority { text-align: center; }
                    .changefreq { text-align: center; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>XML Sitemap</h1>
                    <p class="subtitle">This sitemap contains <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/>
 URLs.</p>

                    <div class="info">
                        This is an XML Sitemap, meant for consumption by search engines like Google, Bing, and Yahoo.
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>Priority</th>
                                <th>Change Frequency</th>
                                <th>Last Modified</th>
                            </tr>
                        </thead>
                        <tbody>
                            <xsl:for-each select="sitemap:urlset/sitemap:url">
                                <tr>
                                    <td>
                                        <a href="{sitemap:loc}">
                                            <xsl:value-of select="sitemap:loc"/>
                                        </a>
                                    </td>
                                    <td class="priority">
                                        <xsl:value-of select="sitemap:priority"/>
                                    </td>
                                    <td class="changefreq">
                                        <xsl:value-of select="sitemap:changefreq"/>
                                    </td>
                                    <td>
                                        <xsl:value-of select="sitemap:lastmod"/>
                                    </td>
                                </tr>
                            </xsl:for-each>
                        </tbody>
                    </table>
                </div>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
