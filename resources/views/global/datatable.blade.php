@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
<x-app-layout :assets="$assets ?? []">
<div>
   <div class="row">
      <div class="col-sm-12">
         <div class="card">
            <div class="card-header d-flex justify-content-between">
               <div class="header-title">
                  <h4 class="card-title">{{ $pageTitle ?? 'List'}}</h4>
               </div>
                <div class="card-action">
                    {!! $headerAction ?? '' !!}
                </div>
            </div>
            <div class="card-body px-2 py-2">
               <div class="table-responsive">
                    {{ $dataTable->table(['class' => 'table text-center table-striped'],true) }}
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</x-app-layout>
<div class="modal fade" id="refused-reservation" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formTitle">Laisser un commentaire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="main_form">
                    <div class="col-md-12">
                        <label class="form-label" for="r_time">Message</label>
                        <textarea type="time" class="form-control"  id="r_time">
                        </textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success" id="btn-continue">
                    Annuler la reservation
                </button>
            </div>
        </div>
    </div>
</div>

