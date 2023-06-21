import {constants} from '../../constants';

export const callUpdateAddonApi = (isCall) => {
    return {type: constants.CALL_UPDATE_ADDON_API, payload: isCall};
};
