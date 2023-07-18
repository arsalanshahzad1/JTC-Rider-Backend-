import {productSubCategoriesActionType} from '../../constants';

export default (state = [], action) => {
    switch (action.type) {
        case productSubCategoriesActionType.FETCH_PRODUCTS_SUBCATEGORIES:
            return action.payload;
        case productSubCategoriesActionType.FETCH_PRODUCT_SUBCATEGORIES:
            return [action.payload];
        case productSubCategoriesActionType.ADD_PRODUCT_SUBCATEGORIES:
            return action.payload;
        case productSubCategoriesActionType.EDIT_PRODUCT_SUBCATEGORIES:
            return state.map(item => item.id === +action.payload.id ? action.payload : item);
        case productSubCategoriesActionType.DELETE_PRODUCT_SUBCATEGORIES:
            return state.filter(item => item.id !== action.payload);
        case productSubCategoriesActionType.FETCH_ALL_PRODUCTS_SUBCATEGORIES:
            return action.payload;
        default:
            return state;
    }
};
