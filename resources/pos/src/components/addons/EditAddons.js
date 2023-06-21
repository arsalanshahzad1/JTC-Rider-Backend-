import React from 'react';
import {connect} from 'react-redux';
import {fetchBrand} from '../../store/action/brandsAction';
import BrandsFrom from './AddonsFrom';
import {getFormattedMessage} from '../../shared/sharedMethod';
import AddonsFrom from "./AddonsFrom";

const EditAddons = (props) => {
    const {handleClose, show, addon} = props;

    return (
        <>
            {addon && <AddonsFrom handleClose={handleClose} show={show} singleAddon={addon}
                                  title={getFormattedMessage('brand.edit.title')}/>}
        </>
    )
};

export default connect(null, {fetchBrand})(EditAddons);

