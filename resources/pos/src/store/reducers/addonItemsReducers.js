import {addonItemsActionType} from '../../constants';

export default (state = [], action) => {
    switch (action.type) {
        case addonItemsActionType.FETCH_ADDON_ITEMS:
            return action.payload;
        case addonItemsActionType.FETCH_ADDON:
            return [action.payload];
        case addonItemsActionType.ADD_ADDON_ITEMS:
            return [...state, action.payload];
        case addonItemsActionType.EDIT_ADDON_ITEMS:
            return state.map(item => item.id === +action.payload.id ? action.payload : item);
        case addonItemsActionType.DELETE_ADDON_ITEMS:
            return state.filter(item => item.id !== action.payload);
        case addonItemsActionType.FETCH_ALL_ADDON_ITEMS:
            return action.payload;
        default:
            return state;
    }
};
