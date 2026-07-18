<style type="text/css">
	@import url("https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&display=swap");
	#paass ul {
  position: relative;
  background: #fff;
  display: flex;
  padding: 10px 20px;
  border-radius: 50px;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

#paass ul li {
  list-style: none;
  line-height: 50px;
  margin: 0 5px;
}

#paass ul li.pageNumber {
  width: 50px;
  height: 50px;
  line-height: 50px;
  text-align: center;
}

 #paass ul li a {
  display: block;
  text-decoration: none;
  color: #383838;
  font-weight: 600;
  border-radius: 50%;
  font-size: 22px;
}

#paass ul li.pageNumber:hover a,
#paass ul li.pageNumber.active a {
  background: #383838;
  color: #fff;
}

#paass ul li:first-child {
  margin-right: 30px;
  font-weight: 700;
  font-size: 20px;
}

#paass ul li:last-child {
  margin-left: 30px;
  font-weight: 700;
  font-size: 20px;
}
	</style>
@if ($paginator->hasPages())
    <ul class="pager" id="paginator">
       
        @if ($paginator->onFirstPage())
            <li class="disabled"><span>← Previous</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev">← Previous</a></li>
        @endif


      
        @foreach ($elements as $element)
           
            @if (is_string($element))
                <li class="disabled"><span>{{ $element }}</span></li>
            @endif


           
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="active my-active"><span>{{ $page }}</span></li>
                    @else
                        <li><a href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach


        
        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next">Next →</a></li>
        @else
            <li class="disabled"><span>Next →</span></li>
        @endif
    </ul>
@endif 