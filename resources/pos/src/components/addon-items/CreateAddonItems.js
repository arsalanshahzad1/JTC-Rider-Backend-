import React, {useState} from 'react';
import { useDispatch } from 'react-redux';
import {Button} from 'react-bootstrap-v5';
import {addAddonItem} from '../../store/action/addonItemsAction';
import BrandsFrom from './AddonItemsFrom';
import {Filters} from '../../constants';
import {getFormattedMessage} from '../../shared/sharedMethod';
import AddonsFrom from "./AddonItemsFrom";
import AddonItemsFrom from "./AddonItemsFrom";

const CreateAddonItems = () => {
    const Dispatch = useDispatch()
    const [show, setShow] = useState(false);
    const handleClose = () => setShow(!show);

    const addAddonItemData = (formValue) => {
        Dispatch(addAddonItem(formValue, Filters.OBJ));
    };

    return (
        <div className='text-end w-sm-auto w-100'>
            <Button variant='primary mb-lg-0 mb-md-0 mb-4' onClick={handleClose}>
                {getFormattedMessage('addon-items.create.title')}
            </Button>
            <AddonItemsFrom addAddonItemData={addAddonItemData} handleClose={handleClose} show={show}
                        title={getFormattedMessage('addon-items.create.title')}/>
        </div>

    )
};

export default CreateAddonItems;
