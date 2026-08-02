const ICONS = {
    'finance-markets': '💹',
    'tax-policy': '🧾',
    technology: '💻',
    'global-economy': '🌍',
    research: '🔬',
    'general-news': '📰',
};

export function departmentIcon(slug) {
    return ICONS[slug] ?? '📰';
}
