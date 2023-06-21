import React, {useState} from 'react';
import { useDispatch } from 'react-redux';
import {Button} from 'react-bootstrap-v5';
import {addAddon, addBrand} from '../../store/action/addonsAction';
import BrandsFrom from './AddonsFrom';
import {Filters} from '../../constants';
import {getFormattedMessage} from '../../shared/sharedMethod';
import AddonsFrom from "./AddonsFrom";

const CreateAddons = () => {
    const Dispatch = useDispatch()
    const [show, setShow] = useState(false);
    const handleClose = () => setShow(!show);

    const addAddonData = (formValue) => {
        Dispatch(addAddon(formValue, Filters.OBJ));
    };

    return (
        <div className='text-end w-sm-auto w-100'>
            <Button variant='primary mb-lg-0 mb-md-0 mb-4' onClick={handleClose}>
                {getFormattedMessage('addon.create.title')}
            </Button>
            <AddonsFrom addAddonData={addAddonData} handleClose={handleClose} show={show}
                        title={getFormattedMessage('addon.create.title')}/>
        </div>

    )
};

export default CreateAddons;
