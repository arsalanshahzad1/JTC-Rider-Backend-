import React, {useState} from 'react';
import {connect} from 'react-redux';
import {Button} from 'react-bootstrap-v5';
import {addProductSubCategory} from '../../store/action/productSubCategoryAction';
import ProductSubCategoryForm from './ProductSubCategoryForm';
import {getFormattedMessage} from '../../shared/sharedMethod';

const CreateProductSubCategory = (props) => {
    const {addProductSubCategory} = props;
    const [show, setShow] = useState(false);
    const handleClose = () => setShow(!show);

    const addProductData = (productValue) => {
        addProductSubCategory(productValue);
    };

    return (
        <div className='text-end w-sm-auto w-100'>
            <Button variant='primary mb-lg-0 mb-md-0 mb-4' onClick={handleClose}>
                {getFormattedMessage('product-subcategory.create.title')}
            </Button>
            <ProductSubCategoryForm addProductData={addProductData} handleClose={handleClose} show={show}
                                 title={getFormattedMessage('product-subcategory.create.title')}/>
        </div>

    )
};

export default connect(null, {addProductSubCategory})(CreateProductSubCategory);
