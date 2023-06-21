import {addonsActionType} from '../../constants';

export default (state = [], action) => {
    switch (action.type) {
        case addonsActionType.FETCH_ADDONS:
            return action.payload;
        case addonsActionType.FETCH_ADDON:
            return [action.payload];
        case addonsActionType.ADD_ADDONS:
            return [...state, action.payload];
        case addonsActionType.EDIT_ADDONS:
            return state.map(item => item.id === +action.payload.id ? action.payload : item);
        case addonsActionType.DELETE_ADDONS:
            return state.filter(item => item.id !== action.payload);
        case addonsActionType.FETCH_ALL_ADDONS:
            return action.payload;
        default:
            return state;
    }
};
