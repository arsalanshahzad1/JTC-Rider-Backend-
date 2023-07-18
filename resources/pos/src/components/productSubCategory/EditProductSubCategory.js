import React from 'react';
import {connect} from 'react-redux';
import ProductSubCategoryForm from './ProductSubCategoryForm';
import {getFormattedMessage} from '../../shared/sharedMethod';

const EditProductSubCategory = (props) => {
    const {handleClose, show, productSubCategory} = props;
    console.log(productSubCategory)
    return (
        <>
            {productSubCategory &&
            <ProductSubCategoryForm handleClose={handleClose} show={show} singleProductSubCategory={productSubCategory}
                                 title={getFormattedMessage('product-subcategory.edit.title')}/>
            }
        </>
    )
};

export default connect(null)(EditProductSubCategory);

