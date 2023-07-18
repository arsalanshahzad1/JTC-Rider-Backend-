import React from 'react';
import {connect} from 'react-redux';
import {deleteProductSubCategory} from '../../store/action/productSubCategoryAction';
import DeleteModel from '../../shared/action-buttons/DeleteModel';
import {getFormattedMessage} from '../../shared/sharedMethod';

const DeleteProductSubCategory = (props) => {
    const {deleteProductSubCategory, onDelete, deleteModel, onClickDeleteModel} = props;

    const deleteUserClick = () => {
        deleteProductSubCategory(onDelete.id);
        onClickDeleteModel(false);
    };

    return (
        <div>
            {deleteModel && <DeleteModel onClickDeleteModel={onClickDeleteModel} deleteModel={deleteModel}
                                         deleteUserClick={deleteUserClick} name={getFormattedMessage('product-subcategory.title')}/>
            }
        </div>
    )
};

export default connect(null, {deleteProductSubCategory})(DeleteProductSubCategory);
