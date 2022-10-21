const mapCurrentRoute = (fullPath, item) => {
    if (typeof item.children === 'object') {
        item.children = item.children.map(i => mapCurrentRoute(fullPath, i))

        return item;
    }

    item.current = fullPath === item.href || fullPath.startsWith(item.href);
    return item;
};

export default {
    state: {
        hideRootNav: Spork.getLocalStorage('hideRootNav', false),
        Features,
    },
    getters: {
        navigation: (state) => Object.values(state.Features)
            .filter(feature => feature.enabled)
            .map(route => ({
                name: route.name,
                icon: route.icon,
                href: route.path,
                group: route.group,
            })).map((item) => mapCurrentRoute(Spork.router.currentRoute._value.fullPath, item))
        .sort((a, b) => a.group < b.group ? -1 : 1)
        .reduce((acc, item) => {
            return {
                ...acc,
                [item.group]: [... new Set([...(acc[item.group] ?? []), item])],
            }
        }, {}),
        hidingRootNav: (state) => state.hideRootNav,
    },
    actions: {
        toggleRootNav({ state }) {
            state.hideRootNav = !state.hideRootNav;
            Spork.setLocalStorage('hideRootNav', state.hideRootNav)
        }
    }
}