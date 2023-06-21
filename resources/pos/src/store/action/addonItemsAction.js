import apiConfig from '../../config/apiConfig';
import {addonItemsActionType, apiBaseURL, brandsActionType, toastType} from '../../constants';
import requestParam from '../../shared/requestParam';
import {addToast} from './toastAction'
import {addInToTotalRecord, setTotalRecord, removeFromTotalRecord} from './totalRecordAction';
import {setLoading} from './loadingAction';
import {getFormattedMessage} from '../../shared/sharedMethod';
import { callUpdateBrandApi } from './updateBrand';

export const fetchAddonItems = (filter = {}, isLoading = true) => async (dispatch) => {
    // console.log(dispatch(),'dispatchfetchAddonItems')
    if (isLoading) {
        dispatch(setLoading(true))
    }
    let url = apiBaseURL.ADDON_ITEMS;
    if (!_.isEmpty(filter) && (filter.page || filter.pageSize || filter.search || filter.order_By || filter.created_at)) {
        url += requestParam(filter);
    }
    apiConfig.get(url)
        .then((response) => {
            console.log(response.data.data,'data.data')
            dispatch({type: addonItemsActionType.FETCH_ADDON_ITEMS, payload: response.data.data});
            // dispatch(setTotalRecord(response.data.meta.total));
            if (isLoading) {
                dispatch(setLoading(false))
            }
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};

export const fetchAddonItem = (brandsId, singleUser) => async (dispatch) => {
    apiConfig.get(apiBaseURL.ADDON_ITEMS + '/' + brandsId, singleUser)
        .then((response) => {
            dispatch({type: addonItemsActionType.FETCH_ADDON_ITEM, payload: response.data.data});
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
}

export const addAddonItem = (addonItems) => async (dispatch) => {
    await apiConfig.post(apiBaseURL.ADDON_ITEMS, addonItems)
        .then((response) => {
            dispatch({type: addonItemsActionType.ADD_ADDON_ITEMS, payload: response.data.data});
            dispatch(addToast({text: getFormattedMessage('addon-item.success.create.message')}));
            // dispatch(addInToTotalRecord(1))
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};
//
export const editAddonItem = (addonItemsId, addonItems, handleClose) => async (dispatch) => {
    console.log(addonItems,'addonItemsaction')
    apiConfig.post(apiBaseURL.ADDON_ITEMS + '/' + addonItemsId ,addonItems)
        .then((response) => {
            // dispatch(callUpdateAddonItemApi(true))
            // dispatch({type: productActionType.ADD_IMPORT_PRODUCT, payload: response.data.data});
            handleClose(false);
            dispatch(addToast({text: getFormattedMessage('addon-item.success.edit.message')}));
            dispatch(addInToTotalRecord(1))
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};
//
// export const deleteAddonItem = (brandsId) => async (dispatch) => {
//     apiConfig.delete(apiBaseURL.BRANDS + '/' + brandsId)
//         .then((response) => {
//             dispatch(removeFromTotalRecord(1));
//             dispatch({type: brandsActionType.DELETE_BRANDS, payload: brandsId});
//             dispatch(addToast({text: getFormattedMessage('brand.success.delete.message')}));
//         })
//         .catch(({response}) => {
//             dispatch(addToast(
//                 {text: response.data.message, type: toastType.ERROR}));
//         });
// };
//
// export const fetchAllAddonItems = () => async (dispatch) => {
//     apiConfig.get(`brands?page[size]=0`)
//         .then((response) => {
//             dispatch({type: brandsActionType.FETCH_ALL_BRANDS, payload: response.data.data});
//         })
//         .catch(({response}) => {
//             dispatch(addToast(
//                 {text: response.data.message, type: toastType.ERROR}));
//         });
// };
