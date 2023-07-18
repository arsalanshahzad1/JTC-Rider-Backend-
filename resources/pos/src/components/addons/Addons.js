import React, {useState, useEffect} from 'react';
import {useDispatch, useSelector} from 'react-redux';
import MasterLayout from '../MasterLayout';
import {fetchAddons} from '../../store/action/addonsAction';
import ReactDataTable from '../../shared/table/ReactDataTable';
import ActionButton from '../../shared/action-buttons/ActionButton';
import DeleteAddons from './DeleteAddons';
import user from '../../assets/images/brand_logo.png';
import CreateAddons from './CreateAddons.js';
import EditAddons from './EditAddons';
import TabTitle from '../../shared/tab-title/TabTitle';
import {getFormattedMessage, placeholderText} from '../../shared/sharedMethod';
import { Tokens } from '../../constants';
import TopProgressBar from "../../shared/components/loaders/TopProgressBar";

const Addons = () => {
    const {addons, totalRecord, isLoading, isCallBrandApi} = useSelector(state => state);
    const Dispatch = useDispatch();
    const [deleteModel, setDeleteModel] = useState(false);
    const [isDelete, setIsDelete] = useState(null);
    const [edit, setEdit] = useState(false);
    const [addon, setAddon] = useState();
    const updatedLanguage = localStorage.getItem(Tokens.UPDATED_LANGUAGE)

    useEffect(() => {
        console.log('addons->>', addons)
      }, []);

    const handleClose = (item) => {
        setEdit(!edit);
        setAddon(item);
    };

    const onClickDeleteModel = (isDelete = null) => {
        setDeleteModel(!deleteModel);
        setIsDelete(isDelete);
    };

    const onChange = (filter) => {
        Dispatch(fetchAddons(filter, true));
    };

    const itemsValue = addons.length >= 0 && addons.map(item => ({
        name: item.attributes.name,
        slug: item.attributes.slug,
        description: item.attributes.description,
        id: item.id
    }));

    const columns = [
        {
            name: getFormattedMessage('addon.table.addon-name.column.label'),
            selector: row => row.name,
            sortable: true,
            sortField: 'name',
            // cell: row => {
            //     const imageUrl = row.image ? row.image : user;
            //     return (
            //         <div className='d-flex align-items-center'>
            //             <div className='me-2'>
            //                 <img src={imageUrl} height='50' width='50' alt='Brand Image'
            //                      className='image image-circle image-mini'/>
            //             </div>
            //             <div className='d-flex flex-column'>
            //                 <span>{row.name}</span>
            //             </div>
            //         </div>
            //     )
            // },
        },
        {
            name: getFormattedMessage('react-data-table.action.column.label'),
            right: true,
            ignoreRowClick: true,
            allowOverflow: true,
            button: true,
            cell: row => <ActionButton item={row} goToEditProduct={handleClose} isEditMode={true}
                                       isDeleteMode={false}
                                       onClickDeleteModel={onClickDeleteModel}/>
        }
    ];
    console.log(addons,'addons..')
    return (
        <MasterLayout>
            <TopProgressBar />
            <TabTitle title={placeholderText('addons.title')}/>
            <ReactDataTable columns={columns} items={itemsValue} onChange={onChange} AddButton={<CreateAddons/>}
                            totalRows={totalRecord} isLoading={isLoading} isCallBrandApi={isCallBrandApi}/>
            <EditAddons handleClose={handleClose} show={edit} addon={addon}/>
            {/*<DeleteAddons onClickDeleteModel={onClickDeleteModel} deleteModel={deleteModel} onDelete={isDelete}/>*/}
        </MasterLayout>
    )
};

export default Addons;

