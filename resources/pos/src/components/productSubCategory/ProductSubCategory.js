import React, { useState, useEffect } from 'react'
import { connect } from 'react-redux'
import MasterLayout from '../MasterLayout'
import { fetchProductSubCategories } from '../../store/action/productSubCategoryAction'
import ReactDataTable from '../../shared/table/ReactDataTable'
import DeleteProductSubCategory from './DeleteProductSubCategory'
import CreateProductSubCategory from './CreateProductSubCategory'
import EditProductSubCategory from './EditProductSubCategory'
import TabTitle from '../../shared/tab-title/TabTitle'
import { getFormattedMessage, placeholderText } from '../../shared/sharedMethod'
import ActionButton from '../../shared/action-buttons/ActionButton'
import TopProgressBar from "../../shared/components/loaders/TopProgressBar";
import apiConfig from '../../config/apiConfig';
// import { Tokens } from '../../constants';

const ProductSubCategory = (props) => {
    const {
        fetchProductSubCategories,
        productSubCategories,
        totalRecord,
        isLoading,
    } = props;
    const [deleteModel, setDeleteModel] = useState(false)
    const [isDelete, setIsDelete] = useState(null)
    const [editModel, setEditModel] = useState(false)
    const [productSubCategory, setProductSubCategory] = useState()
    // const token = localStorage.getItem(Tokens.ADMIN);
    
    const columns = [
    {
        name: getFormattedMessage('product-subcategory.title'),
        selector: row => row.name,
        sortable: true,
        sortField: 'name',
    },
    {
        name: 'Category ID',
        selector: row => row.product_category_id,
        sortable: true,
        sortField: 'name',
    },
    {
        name: getFormattedMessage('react-data-table.action.column.label'),
        right: true,
        ignoreRowClick: true,
        allowOverflow: true,
        button: true,
        cell: row => (
        <ActionButton
            item={row}
            goToEditProduct={handleClose}
            isEditMode={true}
            isDeleteMode={true}
            onClickDeleteModel={onClickDeleteModel}
        />
        ),
    },
    ];

    const itemsValue = productSubCategories.length >= 0 &&  productSubCategories.map(product => ({
        id: product.id,
        name: product.name,
        product_category_id: product.product_category_id,
    }));

    const handleClose = (item) => {
        
        // let fetchPCatData = async () => {
        //             const response = await apiConfig.get(`product-categories/`+ item.product_category_id );
        //             item.category_name = response.data.data.attributes.name;
        //     };
        // fetchPCatData();
        // item.category_name = 'cat';
        // console.log('item', item)
        setEditModel(!editModel)
        setProductSubCategory(item);
    };

    const onClickDeleteModel = (isDelete = null) => {
        setDeleteModel(!deleteModel);
        setIsDelete(isDelete);
    };

    const onChange = (filter) => {
        fetchProductSubCategories(filter, true);
    };

    return (
        <MasterLayout>
            <TopProgressBar />
            <TabTitle title= {placeholderText('product-subcategory.title')}/>
            <ReactDataTable columns={columns} items={itemsValue} onChange={onChange} AddButton={<CreateProductSubCategory/>} totalRows={totalRecord} isLoading={isLoading} />
            <EditProductSubCategory handleClose={handleClose} show={editModel} productSubCategory={productSubCategory}/>
            <DeleteProductSubCategory onClickDeleteModel={onClickDeleteModel} deleteModel={deleteModel} onDelete={isDelete}/>
        </MasterLayout>
    )
};
const mapStateToProps = (state) => {
    const {productSubCategories, totalRecord, isLoading} = state;
    return {productSubCategories, totalRecord, isLoading}
};

export default connect(mapStateToProps, {fetchProductSubCategories})(ProductSubCategory);