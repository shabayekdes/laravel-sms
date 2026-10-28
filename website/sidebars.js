// @ts-check

/** @type {import('@docusaurus/plugin-content-docs').SidebarsConfig} */
const sidebars = {
  guide: [
    {
      type: 'category',
      label: 'Introduction',
      collapsible: false,
      items: ['getting-started', 'gateways'],
    },
    {
      type: 'category',
      label: 'Usage',
      collapsible: false,
      items: ['sending', 'response-codes', 'otp'],
    },
    {
      type: 'category',
      label: 'Extending',
      collapsible: false,
      items: ['custom-drivers'],
    },
    {
      type: 'category',
      label: 'Project',
      collapsible: false,
      items: ['changelog', 'contributing'],
    },
  ],
};

export default sidebars;
