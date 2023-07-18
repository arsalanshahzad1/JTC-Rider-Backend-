import apiConfig from '../../config/apiConfig';
import {apiBaseURL, productSubCategoriesActionType, toastType} from '../../constants';
import {addToast} from './toastAction'
import {addInToTotalRecord, setTotalRecord, removeFromTotalRecord} from './totalRecordAction';
import requestParam from '../../shared/requestParam';
import {setLoading} from './loadingAction';
import {getFormattedMessage} from '../../shared/sharedMethod';

export const fetchProductSubCategories = (filter = {}, isLoading = true) => async (dispatch) => {
    if (isLoading) {
        dispatch(setLoading(true))
    }
    let url = apiBaseURL.PRODUCTS_SUBCATEGORIES
    if (!_.isEmpty(filter) && (filter.page || filter.pageSize || filter.search || filter.order_By || filter.created_at)) {
        url += requestParam(filter);
    }
    apiConfig.get(url)
        .then((response) => {
            dispatch({type: productSubCategoriesActionType.FETCH_PRODUCTS_SUBCATEGORIES, payload: response.data.data});
            dispatch(setTotalRecord(response.data.meta.total));
            if (isLoading) {
                dispatch(setLoading(false))
            }
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};

export const fetchProductSubCategory = (productId, singleProduct) => async (dispatch) => {
    apiConfig.get(apiBaseURL.PRODUCTS_SUBCATEGORIES + '/' + productId, singleProduct)
        .then((response) => {
            dispatch({type: productSubCategoriesActionType.FETCH_PRODUCT_SUBCATEGORIES, payload: response.data.data})
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
}

export const addProductSubCategory = (products) => async (dispatch) => {
    await apiConfig.post(apiBaseURL.PRODUCTS_SUBCATEGORIES, products)
        .then((response) => {
            dispatch({type: productSubCategoriesActionType.ADD_PRODUCT_SUBCATEGORIES, payload: response.data.data});
            dispatch(addToast({text: getFormattedMessage('product-subcategory.success.create.message')}));
            dispatch(addInToTotalRecord(1));
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};

export const editProductSubCategory = (productId, products, handleClose) => async (dispatch) => {
    apiConfig.post(apiBaseURL.PRODUCTS_SUBCATEGORIES + '/' + productId, products)
        .then((response) => {
            dispatch({type: productSubCategoriesActionType.EDIT_PRODUCT_SUBCATEGORIES, payload: response.data.data});
            handleClose(false);
            dispatch(addToast({text: getFormattedMessage('product-subcategory.success.edit.message')}));
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};

export const deleteProductSubCategory = (productId) => async (dispatch) => {
    apiConfig.delete(apiBaseURL.PRODUCTS_SUBCATEGORIES + '/' + productId)
        .then((response) => {
            dispatch(removeFromTotalRecord(1));
            dispatch({type: productSubCategoriesActionType.DELETE_PRODUCT_SUBCATEGORIES, payload: productId});
            dispatch(addToast({text: getFormattedMessage('product-subcategory.success.delete.message')}));
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};

export const fetchAllProductSubCategories = () => async (dispatch) => {
    apiConfig.get(`product-subcategories?page[size]=0`)
        .then((response) => {
            dispatch({type: productSubCategoriesActionType.FETCH_ALL_PRODUCTS_SUBCATEGORIES, payload: response.data.data});
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};
