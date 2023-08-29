<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\GeneratorCommand;

class Enum extends GeneratorCommand
{
    /**
     * 运行的命令
     *
     * @var string
     */
    protected $name = 'make:enum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成枚举类';

    /**
     * 这里是声明生成的文件类型,在新建成功时,命令行会显示xxx created successfully.这里的xxx在这就是Download
     *
     * @var string
     */
    protected $type = 'Enum';


    /**
     * 文件模板的地址,这里就是第一步新建的stub文件的路径
     * @return string
     */
    public function getStub(): string
    {
        return base_path('stubs/enum.stub');
    }

    /**
     * 获取文件的默认的命名空间,因为文件打算建立在app下的Exports目录下,所以修改了一下这个文件,若要生成在app下,可不重写此方法
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Enums';
    }

}
