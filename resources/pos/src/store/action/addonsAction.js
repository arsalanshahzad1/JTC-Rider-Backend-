import apiConfig from '../../config/apiConfig';
import {addonsActionType, apiBaseURL, brandsActionType, toastType} from '../../constants';
import requestParam from '../../shared/requestParam';
import {addToast} from './toastAction'
import {addInToTotalRecord, setTotalRecord, removeFromTotalRecord} from './totalRecordAction';
import {setLoading} from './loadingAction';
import {getFormattedMessage} from '../../shared/sharedMethod';
import { callUpdateBrandApi } from './updateBrand';

export const fetchAddons = (filter = {}, isLoading = true) => async (dispatch) => {
    // console.log(dispatch(),'dispatchfetchAddons')
    if (isLoading) {
        dispatch(setLoading(true))
    }
    let url = apiBaseURL.ADDONS;
    if (!_.isEmpty(filter) && (filter.page || filter.pageSize || filter.search || filter.order_By || filter.created_at)) {
        url += requestParam(filter);
    }
    apiConfig.get(url)
        .then((response) => {
            console.log(response.data.data,'data.data')
            dispatch({type: addonsActionType.FETCH_ADDONS, payload: response.data.data});
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

// export const fetchAddon = (brandsId, singleUser) => async (dispatch) => {
//     apiConfig.get(apiBaseURL.BRANDS + '/' + brandsId, singleUser)
//         .then((response) => {
//             dispatch({type: brandsActionType.FETCH_BRAND, payload: response.data.data});
//         })
//         .catch(({response}) => {
//             dispatch(addToast(
//                 {text: response.data.message, type: toastType.ERROR}));
//         });
// }
//
export const addAddon = (addons) => async (dispatch) => {
    await apiConfig.post(apiBaseURL.ADDONS, addons)
        .then((response) => {
            dispatch({type: addonsActionType.ADD_ADDONS, payload: response.data.data});
            dispatch(addToast({text: getFormattedMessage('addon.success.create.message')}));
            // dispatch(addInToTotalRecord(1))
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};
//
export const editAddon = (addonsId, addons, handleClose) => async (dispatch) => {
    console.log(addons,'addonsaction')
    apiConfig.post(apiBaseURL.ADDONS + '/' + addonsId ,addons)
        .then((response) => {
            // dispatch(callUpdateAddonApi(true))
            // dispatch({type: productActionType.ADD_IMPORT_PRODUCT, payload: response.data.data});
            handleClose(false);
            dispatch(addToast({text: getFormattedMessage('addon.success.edit.message')}));
            dispatch(addInToTotalRecord(1))
        })
        .catch(({response}) => {
            dispatch(addToast(
                {text: response.data.message, type: toastType.ERROR}));
        });
};
//
// export const deleteAddon = (brandsId) => async (dispatch) => {
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
// export const fetchAllAddons = () => async (dispatch) => {
//     apiConfig.get(`brands?page[size]=0`)
//         .then((response) => {
//             dispatch({type: brandsActionType.FETCH_ALL_BRANDS, payload: response.data.data});
//         })
//         .catch(({response}) => {
//             dispatch(addToast(
//                 {text: response.data.message, type: toastType.ERROR}));
//         });
// };
