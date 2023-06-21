import React from 'react';
import {connect} from 'react-redux';
import {fetchAddonItem} from '../../store/action/addonItemsAction';
import BrandsFrom from './AddonItemsFrom';
import {getFormattedMessage} from '../../shared/sharedMethod';
import AddonsFrom from "./AddonItemsFrom";

const EditAddonItems = (props) => {
    const {handleClose, show, addonItem} = props;

    return (
        <>
            {addonItem && <AddonsFrom handleClose={handleClose} show={show} singleAddonItem={addonItem}
                                  title={getFormattedMessage('addon-items.create.title')}/>}
        </>
    )
};

export default connect(null, {fetchAddonItem})(EditAddonItems);

